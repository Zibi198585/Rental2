<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class RentalReturnProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'rental_return_id',
        'product_id',
        'related_issue_product_ids',
        'quantity',
        'actual_rental_days',
        'daily_rate',
        'total_rental_cost',
        'condition',
        'condition_notes',
        'returned_serial_numbers',
        'damage_cost',
        'late_fee',
        'cleaning_fee',
        'other_fees',
        'fees_description',
        'issue_date',
        'return_date',
        'status',
    ];

    protected $casts = [
        'related_issue_product_ids' => 'array',
        'quantity' => 'integer',
        'actual_rental_days' => 'integer',
        'daily_rate' => MoneyCast::class,
        'total_rental_cost' => MoneyCast::class,
        'damage_cost' => MoneyCast::class,
        'late_fee' => MoneyCast::class,
        'cleaning_fee' => MoneyCast::class,
        'other_fees' => MoneyCast::class,
        'issue_date' => 'date',
        'return_date' => 'date',
    ];

    // Relacje
    public function rentalReturn(): BelongsTo
    {
        return $this->belongsTo(RentalReturn::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function relatedIssueProducts()
    {
        if (empty($this->related_issue_product_ids)) {
            return collect();
        }
        
        return RentalIssueProduct::whereIn('id', $this->related_issue_product_ids)->get();
    }

    // Automatyczne obliczenia
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($returnProduct) {
            // Oblicz total_rental_cost jeśli nie jest ustawiony
            if ($returnProduct->isDirty(['daily_rate', 'actual_rental_days', 'quantity'])) {
                $returnProduct->total_rental_cost = 
                    $returnProduct->daily_rate * 
                    $returnProduct->actual_rental_days * 
                    $returnProduct->quantity;
            }

            // Oblicz actual_rental_days jeśli są daty
            if ($returnProduct->issue_date && $returnProduct->return_date && !$returnProduct->actual_rental_days) {
                $returnProduct->actual_rental_days = $returnProduct->issue_date->diffInDays($returnProduct->return_date) + 1;
            }
        });
    }

    // Akcesory
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'returned' => 'Zwrócone',
            'inspected' => 'Sprawdzone',
            'processed' => 'Przetworzone',
            'damaged' => 'Uszkodzone',
            default => $this->status,
        };
    }

    public function getConditionLabelAttribute(): string
    {
        return match($this->condition) {
            'excellent' => 'Doskonały',
            'good' => 'Dobry',
            'fair' => 'Przeciętny',
            'poor' => 'Słaby',
            'damaged' => 'Uszkodzony',
            default => $this->condition,
        };
    }

    public function getTotalFeesAttribute(): float
    {
        return $this->damage_cost + $this->late_fee + $this->cleaning_fee + $this->other_fees;
    }

    public function getGrandTotalAttribute(): float
    {
        return $this->total_rental_cost + $this->getTotalFeesAttribute();
    }

    public function getHasFeesAttribute(): bool
    {
        return $this->getTotalFeesAttribute() > 0;
    }

    public function getHasDamagesAttribute(): bool
    {
        return $this->damage_cost > 0 || $this->condition === 'damaged';
    }

    public function getWasLateAttribute(): bool
    {
        return $this->late_fee > 0;
    }

    public function getRentalDaysTextAttribute(): string
    {
        $days = $this->actual_rental_days ?? 0;
        
        if ($days === 1) {
            return '1 dzień';
        } elseif ($days < 5) {
            return $days . ' dni';
        } else {
            return $days . ' dni';
        }
    }

    // Metody pomocnicze
    public function addRelatedIssueProduct(int $issueProductId): void
    {
        $relatedIds = $this->related_issue_product_ids ?? [];
        
        if (!in_array($issueProductId, $relatedIds)) {
            $relatedIds[] = $issueProductId;
            $this->related_issue_product_ids = $relatedIds;
            $this->save();
        }
    }

    public function removeRelatedIssueProduct(int $issueProductId): void
    {
        $relatedIds = $this->related_issue_product_ids ?? [];
        $relatedIds = array_filter($relatedIds, fn($id) => $id !== $issueProductId);
        
        $this->related_issue_product_ids = array_values($relatedIds);
        $this->save();
    }

    public function calculateLateFee(Carbon $plannedReturnDate, float $dailyLateFeeRate = 0.1): float
    {
        if (!$this->return_date || $this->return_date->lte($plannedReturnDate)) {
            return 0;
        }

        $lateDays = $plannedReturnDate->diffInDays($this->return_date);
        return $this->daily_rate * $dailyLateFeeRate * $lateDays * $this->quantity;
    }

    public function updateRelatedIssueProducts(): void
    {
        foreach ($this->relatedIssueProducts() as $issueProduct) {
            $issueProduct->addReturn($this->quantity);
        }
    }

    // Metody dla numerów seryjnych
    public function getReturnedSerialNumbersArrayAttribute(): array
    {
        if (empty($this->returned_serial_numbers)) {
            return [];
        }

        // Sprawdź czy to JSON czy zwykły string
        $decoded = json_decode($this->returned_serial_numbers, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        // Jeśli nie JSON, podziel po przecinkach
        return array_map('trim', explode(',', $this->returned_serial_numbers));
    }

    public function setReturnedSerialNumbersArray(array $serialNumbers): void
    {
        $this->returned_serial_numbers = json_encode($serialNumbers);
    }

    public function addReturnedSerialNumber(string $serialNumber): void
    {
        $serialNumbers = $this->getReturnedSerialNumbersArrayAttribute();
        if (!in_array($serialNumber, $serialNumbers)) {
            $serialNumbers[] = $serialNumber;
            $this->setReturnedSerialNumbersArray($serialNumbers);
        }
    }

    // Weryfikacja integralności danych
    public function validateReturn(): array
    {
        $errors = [];

        if ($this->quantity <= 0) {
            $errors[] = 'Ilość musi być większa od 0';
        }

        if ($this->daily_rate <= 0) {
            $errors[] = 'Stawka dzienna musi być większa od 0';
        }

        if ($this->actual_rental_days <= 0) {
            $errors[] = 'Liczba dni wynajmu musi być większa od 0';
        }

        if ($this->issue_date && $this->return_date && $this->return_date->lt($this->issue_date)) {
            $errors[] = 'Data zwrotu nie może być wcześniejsza niż data wydania';
        }

        return $errors;
    }
}
