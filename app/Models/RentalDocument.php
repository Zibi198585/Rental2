<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Casts\MoneyCast;
use Illuminate\Support\Carbon;

class RentalDocument extends Model
{
    protected $fillable = [
        'agreement_number',
        'status', // Added status field
        'city',
        'contractor_full_name',

        // Address
        'address_street',
        'address_building_number',
        'address_apartment_number',
        'address_postal_code',
        'address_city',
        'address_voivodeship', // Changed from state to voivodeship
        'address_country',

        // Document type
        'document_type',
        'other_document',
        'document_number',
        'pesel', // PESEL
        'nip',      // NIP

        // Contact
        'contact_phone',
        'contact_email',

        // Dates and rental info
        'rental_date',
        'expected_return_date',
        'real_return_date', // Rzeczywista data zwrotu
        'rental_days',

        'equipment_location',

        'deposit',

        // Delivery
        'delivery_method',
        'delivery_cost',
        'pickup_cost',

        // Podsumowania
        'summary_products_per_day',
        'summary_products_total',
        'summary_delivery',
        'summary_deposit',
        'summary_total',
        'summary_products_total_per_day',
        'summary_products_total_period',
        'summary_delivery_total_period',
        'summary_net_period',
        'summary_vat_period',
        'summary_gross_period',
        'vat_rate', // Stawka VAT, domyślnie 23%
    ];

    protected $casts = [
        'delivery_cost' => MoneyCast::class,
        'pickup_cost'   => MoneyCast::class,
        'deposit'       => MoneyCast::class,

        // Podsumowania
        'summary_products_per_day' => MoneyCast::class,
        'summary_products_total' => MoneyCast::class,
        'summary_delivery' => MoneyCast::class,
        'summary_deposit' => MoneyCast::class,
        'summary_total' => MoneyCast::class,
        'summary_products_total_per_day' => MoneyCast::class,
        'summary_products_total_period' => MoneyCast::class,
        'summary_delivery_total_period' => MoneyCast::class,
        'summary_net_period' => MoneyCast::class,
        'summary_vat_period' => MoneyCast::class,
        'summary_gross_period' => MoneyCast::class,
    ];

    public function getRentalPeriodInfoAttribute(): string
    {
        if (!$this->rental_date || !$this->expected_return_date) {
            return '';
        }

        $rentalDate = Carbon::parse($this->rental_date)->format('d.m.Y');
        $returnDate = Carbon::parse($this->expected_return_date)->format('d.m.Y');
        $now = Carbon::now()->startOfDay();
        $expectedReturn = Carbon::parse($this->expected_return_date)->startOfDay();

        // Tłumaczenia statusów
        $status = $this->status;
        if ($status === 'draft') {
            return "{$rentalDate} → {$returnDate} (Umowa nieaktywna)";
        }

        if (in_array($status, ['rented', 'partially_returned', 'scheduled_return'])) {
            $daysLeft = $now->diffInDays($expectedReturn, false);

            if ($daysLeft > 1) {
                $daysText = "Przewidywany zwrot za {$daysLeft} dni";
            } elseif ($daysLeft === 1) {
                $daysText = "Przewidywany zwrot za 1 dzień";
            } elseif ($daysLeft === 0) {
                $daysText = "Zwrot dzisiaj";
            } elseif ($daysLeft < 0) {
                $dni = abs($daysLeft);
                $dzien = ($dni === 1) ? 'dzień' : 'dni';
                $daysText = "❗️Zwrot opóźniony o {$dni} {$dzien}!";
            } else {
                $daysText = "";
            }
            return "{$rentalDate} → {$returnDate}" . ($daysText ? " ({$daysText})" : "");
        }

        if ($status === 'returned') {
            $realReturn = $this->real_return_date
                ? Carbon::parse($this->real_return_date)->startOfDay()
                : $expectedReturn;
            $diff = $realReturn->diffInDays($expectedReturn, false);

            if ($diff === 0) {
                $info = "✅ Zwrócono w terminie";
            } elseif ($diff < 0) {
                $info = "🟢 Zwrócono " . abs($diff) . " dni przed terminem";
            } else {
                $info = "❗️Zwrócono " . abs($diff) . " dni po terminie";
            }
            $realReturnText = $this->real_return_date
                ? $realReturn->format('d.m.Y')
                : $returnDate;
            return "{$rentalDate} → {$returnDate} (Zwrócono: {$realReturnText}, {$info})";
        }

        return "{$rentalDate} → {$returnDate}";
    }

    protected static function booted()
    {
        static::saving(function (self $model) {
            if (
                $model->isDirty('status') &&
                $model->status === 'rented' &&
                empty($model->agreement_number)
            ) {
                $model->agreement_number = self::generateAgreementNumber($model->rental_date);
            }
        });
    }

    public static function generateAgreementNumber(?string $rentalDate = null): string
    {
        $prefix = 'UW';

        // Ustal datę na podstawie rental_date lub aktualnej daty
        $date = $rentalDate ? Carbon::parse($rentalDate) : now();
        $month = $date->format('m');
        $year = $date->format('Y');

        // Znajdź ostatni numer dla danego miesiąca i roku
        $last = self::whereYear('rental_date', $year)
            ->whereMonth('rental_date', $month)
            ->whereNotNull('agreement_number')
            ->orderByDesc('agreement_number')
            ->first();

        // Wyciągnij numer z ostatniej umowy (np. UW/7/05/2025)
        $lastNumber = 0;
        if ($last && preg_match('/^UW\/(\d+)\/\d{2}\/\d{4}$/', $last->agreement_number, $matches)) {
            $lastNumber = (int) $matches[1];
        }

        $newNumber = $lastNumber + 1;
        return "{$prefix}/{$newNumber}/{$month}/{$year}";
    }

    public function products()
    {
        return $this->hasMany(RentalDocumentProduct::class);
    }

    public function rentalIssues()
    {
        return $this->hasMany(RentalIssue::class);
    }

    public function rentalReturns()
    {
        return $this->hasManyThrough(
            RentalReturn::class,
            RentalIssue::class,
            'rental_document_id', // foreign key on rental_issues table
            'related_rental_document_ids', // foreign key on rental_returns table (JSON field)
            'id', // local key on rental_documents table
            'id' // local key on rental_issues table
        );
    }

    /**
     * Oblicza pozostałą kwotę kaucji po uwzględnieniu kosztów dostawy i dziennych kosztów wynajmu
     */
    public function getDepositBalanceAttribute(): array
    {
        if (!$this->deposit || $this->status === 'draft') {
            return [
                'remaining' => $this->deposit ?? 0,
                'used' => 0,
                'days_passed' => 0,
                'days_covered_by_deposit' => 0,
                'planned_days' => $this->rental_days ?? 0,
                'daily_cost' => 0,
                'delivery_cost' => ($this->delivery_cost ?? 0) + ($this->pickup_cost ?? 0),
                'balance_difference' => 0,
                'status' => 'inactive'
            ];
        }

        // Całkowity koszt dostawy (dostawa + odbiór)
        $deliveryCost = ($this->delivery_cost ?? 0) + ($this->pickup_cost ?? 0);
        
        // Dzienny koszt produktów
        $dailyCost = $this->summary_products_per_day ?? $this->calculateDailyCostFromProducts();
        
        // Kaucja po odjęciu kosztów dostawy
        $remainingAfterDelivery = $this->deposit - $deliveryCost;
        
        // Oblicz ile dni pokryje kaucja
        $daysCoveredByDeposit = $dailyCost > 0 ? floor($remainingAfterDelivery / $dailyCost) : 0;
        
        // Oblicz ile dni rzeczywiście minęło
        $daysPassed = $this->calculateDaysPassed();
        $plannedDays = $this->rental_days ?? 0;
        
        // Oblicz aktualne wykorzystanie kaucji
        $currentUsage = $daysPassed * $dailyCost;
        $currentRemaining = $remainingAfterDelivery - $currentUsage;
        
        // Oblicz prognozę końcową (dla planowanych dni)
        $totalRentalCost = $plannedDays * $dailyCost;
        $balanceDifference = $remainingAfterDelivery - $totalRentalCost;

        return [
            'remaining' => max(0, $currentRemaining),
            'used' => $deliveryCost + $currentUsage,
            'days_passed' => $daysPassed,
            'days_covered_by_deposit' => $daysCoveredByDeposit,
            'planned_days' => $plannedDays,
            'daily_cost' => $dailyCost,
            'delivery_cost' => $deliveryCost,
            'balance_difference' => $balanceDifference, // + zwrot, - dopłata
            'total_rental_cost' => $totalRentalCost,
            'status' => $this->determineDepositStatus($currentRemaining, $balanceDifference, $this->status)
        ];
    }

    /**
     * Zwraca czytelny tekst stanu kaucji dla wyświetlenia w tabeli
     */
    public function getDepositBalanceTextAttribute(): string
    {
        $balance = $this->deposit_balance;
        
        if ($balance['status'] === 'inactive') {
            return $this->deposit ? $this->formatMoney($this->deposit) . ' (nieaktywne)' : 'Brak kaucji';
        }

        // Dla zwróconych produktów - pokaż ostateczną różnicę
        if ($this->status === 'returned') {
            $difference = $balance['balance_difference'];
            if ($difference > 0) {
                return '🟢 Zwrot: ' . $this->formatMoney($difference);
            } elseif ($difference < 0) {
                return '🔴 Dopłata: ' . $this->formatMoney(abs($difference));
            } else {
                return '✅ Rozliczone dokładnie';
            }
        }

        // Dla aktywnych wynajmów - pokaż postęp względem dni pokrytych kaucją
        $remaining = $this->formatMoney($balance['remaining']);
        $daysPassed = $balance['days_passed'];
        $daysCovered = $balance['days_covered_by_deposit'];
        $plannedDays = $balance['planned_days'];
        
        // Progres względem pokrycia kaucji
        $progress = "{$daysPassed}/{$daysCovered} dni";
        
        // Prognoza końcowa na podstawie planowanych dni
        $finalDifference = $balance['balance_difference'];
        if ($finalDifference > 0) {
            $forecast = ' (prognoza: zwrot ' . $this->formatMoney($finalDifference) . ')';
        } elseif ($finalDifference < 0) {
            $forecast = ' (prognoza: dopłata ' . $this->formatMoney(abs($finalDifference)) . ')';
        } else {
            $forecast = ' (prognoza: dokładnie)';
        }
        
        // Status na podstawie tego czy przekroczono dni pokryte kaucją
        if ($daysPassed > $daysCovered) {
            return "💸 Przekroczono kaucję ({$progress}){$forecast}";
        }
        
        return match($balance['status']) {
            'safe' => "✅ {$remaining} ({$progress}){$forecast}",
            'warning' => "⚠️ {$remaining} ({$progress}){$forecast}", 
            'critical' => "❌ {$remaining} ({$progress}){$forecast}",
            'exhausted' => "💸 Wyczerpana ({$progress}){$forecast}",
            default => "{$remaining} ({$progress}){$forecast}"
        };
    }

    /**
     * Formatuje kwotę na złote z odpowiednim formatem
     */
    private function formatMoney(float $amount): string
    {
        return number_format($amount, 2, ',', ' ') . ' zł';
    }

    /**
     * Oblicza dzienny koszt z produktów, jeśli nie ma summary_products_total_per_day
     */
    private function calculateDailyCostFromProducts(): float
    {
        return $this->products->sum(function ($product) {
            return ($product->price_per_day ?? 0) * ($product->quantity ?? 1);
        });
    }

    /**
     * Oblicza ile dni minęło od daty wynajmu
     */
    private function calculateDaysPassed(): int
    {
        if (!$this->rental_date || in_array($this->status, ['draft', 'returned'])) {
            return 0;
        }

        $rentalDate = Carbon::parse($this->rental_date)->startOfDay();
        $today = Carbon::now()->startOfDay();
        
        // Dla zwróconych produktów, użyj rzeczywistej daty zwrotu jeśli istnieje
        if ($this->status === 'returned' && isset($this->real_return_date)) {
            $endDate = Carbon::parse($this->real_return_date)->startOfDay();
            return max(0, $rentalDate->diffInDays($endDate));
        }
        
        // Dla aktywnych wynajmów, użyj dzisiejszej daty, ale nie więcej niż planowane dni
        $daysPassed = $rentalDate->diffInDays($today);
        $maxDays = $this->rental_days ?? PHP_INT_MAX;
        
        return min($daysPassed, $maxDays);
    }

    /**
     * Określa status kaucji na podstawie pozostałej kwoty i prognozy
     */
    private function determineDepositStatus(float $currentRemaining, float $balanceDifference, string $status): string
    {
        if ($currentRemaining <= 0) {
            return 'exhausted';
        }
        
        // Dla zwróconych - status na podstawie ostatecznego rozliczenia
        if ($status === 'returned') {
            return $balanceDifference >= 0 ? 'safe' : 'critical';
        }
        
        // Dla aktywnych wynajmów - oceń na podstawie prognozy końcowej
        if ($balanceDifference < 0) {
            // Prognoza dopłaty = krytyczny
            return 'critical';
        } elseif ($balanceDifference < ($this->deposit * 0.1)) {
            // Niska prognoza zwrotu = ostrzeżenie
            return 'warning';  
        }
        
        return 'safe';
    }
}


