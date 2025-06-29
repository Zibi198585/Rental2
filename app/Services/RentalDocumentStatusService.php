<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\RentalDocument;
use App\Models\RentalIssue;
use App\Models\RentalReturn;
use App\Models\Product;
use Illuminate\Support\Collection;

class RentalDocumentStatusService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Pobiera pełny status wydań/zwrotów dla umowy wynajmu
     */
    public function getDocumentStatus(RentalDocument $document): array
    {
        $plannedProducts = $this->getPlannedProducts($document);
        $issuedProducts = $this->getIssuedProducts($document);
        $returnedProducts = $this->getReturnedProducts($document);

        $statusProducts = [];
        
        // Zbierz wszystkie unikalne produkty (zaplanowane + wydane + zwrócone)
        $allProductIds = array_unique(array_merge(
            array_keys($plannedProducts),
            array_keys($issuedProducts), 
            array_keys($returnedProducts)
        ));

        foreach ($allProductIds as $productId) {
            $planned = $plannedProducts[$productId] ?? 0;
            $issued = $issuedProducts[$productId] ?? 0;
            $returned = $returnedProducts[$productId] ?? 0;
            $inCirculation = $issued - $returned;
            $remainingToIssue = max(0, $planned - $issued);

            $product = Product::find($productId);

            $statusProducts[] = [
                'product_id' => $productId,
                'product_name' => $product?->name ?? 'Nieznany produkt',
                'product_unit' => $product?->unit ?? 'szt.',
                'planned_quantity' => $planned,
                'issued_quantity' => $issued,
                'returned_quantity' => $returned,
                'in_circulation' => $inCirculation,
                'remaining_to_issue' => $remainingToIssue,
                'status' => $this->determineProductStatus($planned, $issued, $returned),
                'daily_cost' => $product?->price_per_day ?? 0,
            ];
        }

        return [
            'document' => $document,
            'products' => $statusProducts,
            'summary' => $this->calculateSummary($statusProducts),
            'issues' => $this->getDocumentIssues($document),
            'returns' => $this->getDocumentReturns($document),
        ];
    }

    /**
     * Pobiera produkty zaplanowane w umowie (grupowane i zsumowane)
     */
    private function getPlannedProducts(RentalDocument $document): array
    {
        return $document->products()
            ->get()
            ->groupBy('product_id')
            ->map(function ($products) {
                return $products->sum('quantity');
            })
            ->toArray();
    }

    /**
     * Pobiera sumy wydanych produktów ze wszystkich wydań dla umowy
     */
    private function getIssuedProducts(RentalDocument $document): array
    {
        return $document->rentalIssues()
            ->with('products')
            ->get()
            ->flatMap(function ($issue) {
                return $issue->products;
            })
            ->groupBy('product_id')
            ->map(function ($products) {
                return $products->sum('quantity');
            })
            ->toArray();
    }

    /**
     * Pobiera sumy zwróconych produktów ze wszystkich zwrotów powiązanych z umową
     */
    private function getReturnedProducts(RentalDocument $document): array
    {
        // Pobieramy wszystkie zwroty powiązane z wydaniami tej umowy
        $documentIssueIds = $document->rentalIssues()->pluck('id')->toArray();
        
        if (empty($documentIssueIds)) {
            return [];
        }

        return RentalReturn::whereJsonContains('related_issue_ids', $documentIssueIds)
            ->orWhere(function ($query) use ($document) {
                $query->whereJsonContains('related_rental_document_ids', $document->id);
            })
            ->with('products')
            ->get()
            ->flatMap(function ($return) {
                return $return->products;
            })
            ->groupBy('product_id')
            ->map(function ($products) {
                return $products->sum('quantity');
            })
            ->toArray();
    }

    /**
     * Pobiera wszystkie wydania dla umowy
     */
    private function getDocumentIssues(RentalDocument $document): Collection
    {
        return $document->rentalIssues()
            ->with('products.product')
            ->orderBy('issue_date', 'desc')
            ->get();
    }

    /**
     * Pobiera wszystkie zwroty powiązane z umową
     */
    private function getDocumentReturns(RentalDocument $document): Collection
    {
        $documentIssueIds = $document->rentalIssues()->pluck('id')->toArray();
        
        if (empty($documentIssueIds)) {
            return collect();
        }

        return RentalReturn::where(function ($query) use ($documentIssueIds, $document) {
                $query->whereJsonContains('related_issue_ids', $documentIssueIds)
                      ->orWhereJsonContains('related_rental_document_ids', $document->id);
            })
            ->with('products.product')
            ->orderBy('return_date', 'desc')
            ->get();
    }

    /**
     * Określa status produktu
     */
    private function determineProductStatus(int $planned, int $issued, int $returned): string
    {
        $inCirculation = $issued - $returned;

        if ($planned === 0 && $issued === 0) {
            return 'not_planned';
        }

        if ($planned === 0 && $issued > 0) {
            // Wydano więcej niż planowano (lub nie było w ogóle planowane)
            if ($inCirculation === 0) {
                return 'extra_returned'; // wydano dodatkowo i zwrócono
            }
            return 'extra_issued'; // wydano dodatkowo i jest w obiegu
        }

        if ($issued === 0) {
            return 'not_issued';
        }

        if ($issued < $planned) {
            return 'partially_issued';
        }

        if ($issued > $planned) {
            // Wydano więcej niż planowano
            if ($inCirculation === 0) {
                return 'over_issued_returned';
            }
            return 'over_issued';
        }

        if ($inCirculation === 0) {
            return 'fully_returned';
        }

        if ($returned > 0 && $inCirculation > 0) {
            return 'partially_returned';
        }

        return 'fully_issued';
    }

    /**
     * Oblicza podsumowanie dla całej umowy
     */
    private function calculateSummary(array $statusProducts): array
    {
        $totalPlanned = array_sum(array_column($statusProducts, 'planned_quantity'));
        $totalIssued = array_sum(array_column($statusProducts, 'issued_quantity'));
        $totalReturned = array_sum(array_column($statusProducts, 'returned_quantity'));
        $totalInCirculation = array_sum(array_column($statusProducts, 'in_circulation'));
        $totalRemainingToIssue = array_sum(array_column($statusProducts, 'remaining_to_issue'));

        return [
            'total_planned' => $totalPlanned,
            'total_issued' => $totalIssued,
            'total_returned' => $totalReturned,
            'total_in_circulation' => $totalInCirculation,
            'total_remaining_to_issue' => $totalRemainingToIssue,
            'completion_percentage' => $totalPlanned > 0 ? round(($totalIssued / $totalPlanned) * 100, 1) : 0,
            'return_percentage' => $totalIssued > 0 ? round(($totalReturned / $totalIssued) * 100, 1) : 0,
        ];
    }

    /**
     * Pobiera status dla wszystkich aktywnych umów
     */
    public function getAllActiveDocumentsStatus(): Collection
    {
        return RentalDocument::whereIn('status', ['rented', 'partially_returned', 'scheduled_return'])
            ->with(['products.product', 'rentalIssues.products'])
            ->get()
            ->map(function ($document) {
                return $this->getDocumentStatus($document);
            });
    }



    /**
     * Pobiera etykiety statusów
     */
    public static function getStatusLabels(): array
    {
        return [
            'not_planned' => 'Nieplanowany',
            'not_issued' => 'Niewydany',
            'partially_issued' => 'Częściowo wydany',
            'fully_issued' => 'Całkowicie wydany',
            'partially_returned' => 'Częściowo zwrócony',
            'fully_returned' => 'Całkowicie zwrócony',
            'extra_issued' => 'Wydano dodatkowo',
            'extra_returned' => 'Wydano dodatkowo i zwrócono',
            'over_issued' => 'Wydano ponad plan',
            'over_issued_returned' => 'Wydano ponad plan i zwrócono',
        ];
    }

    /**
     * Pobiera kolory dla statusów
     */
    public static function getStatusColors(): array
    {
        return [
            'not_planned' => 'gray',
            'not_issued' => 'warning',
            'partially_issued' => 'info',
            'fully_issued' => 'success',
            'partially_returned' => 'primary',
            'fully_returned' => 'success',
            'extra_issued' => 'danger',
            'extra_returned' => 'danger',
            'over_issued' => 'danger',
            'over_issued_returned' => 'danger',
        ];
    }
}
