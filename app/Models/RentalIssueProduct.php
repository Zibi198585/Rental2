<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class RentalIssueProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'rental_issue_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
        'technical_notes',
        'serial_numbers',
        'condition_before',
        'planned_return_date',
        'planned_rental_days',
        'status',
        'returned_quantity',
    ];

    protected $casts = [
        'unit_price' => MoneyCast::class,
        'total_price' => MoneyCast::class,
        'planned_return_date' => 'date',
        'quantity' => 'integer',
        'returned_quantity' => 'integer',
        'planned_rental_days' => 'integer',
    ];

    // Relacje
    public function rentalIssue(): BelongsTo
    {
        return $this->belongsTo(RentalIssue::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Automatyczne obliczenie total_price
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($issueProduct) {
            $issueProduct->total_price = $issueProduct->quantity * $issueProduct->unit_price;
        });
    }

    // Akcesory
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'issued' => 'Wydane',
            'partially_returned' => 'Częściowo zwrócone',
            'fully_returned' => 'Całkowicie zwrócone',
            default => $this->status,
        };
    }

    public function getRemainingQuantityAttribute(): int
    {
        return $this->quantity - $this->returned_quantity;
    }

    public function getReturnPercentageAttribute(): float
    {
        if ($this->quantity === 0) {
            return 0;
        }
        
        return ($this->returned_quantity / $this->quantity) * 100;
    }

    public function getIsFullyReturnedAttribute(): bool
    {
        return $this->returned_quantity >= $this->quantity;
    }

    public function getIsPartiallyReturnedAttribute(): bool
    {
        return $this->returned_quantity > 0 && $this->returned_quantity < $this->quantity;
    }

    public function getDaysUntilPlannedReturnAttribute(): ?int
    {
        if (!$this->planned_return_date) {
            return null;
        }

        return (int) Carbon::now()->diffInDays($this->planned_return_date, false);
    }

    public function getIsOverdueAttribute(): bool
    {
        if (!$this->planned_return_date) {
            return false;
        }

        return Carbon::now()->isAfter($this->planned_return_date) && !$this->is_fully_returned;
    }

    // Metody pomocnicze
    public function addReturn(int $quantity): bool
    {
        if ($quantity <= 0 || $this->returned_quantity + $quantity > $this->quantity) {
            return false;
        }

        $this->returned_quantity += $quantity;
        $this->updateStatus();
        $this->save();

        return true;
    }

    public function removeReturn(int $quantity): bool
    {
        if ($quantity <= 0 || $this->returned_quantity - $quantity < 0) {
            return false;
        }

        $this->returned_quantity -= $quantity;
        $this->updateStatus();
        $this->save();

        return true;
    }

    private function updateStatus(): void
    {
        if ($this->returned_quantity === 0) {
            $this->status = 'issued';
        } elseif ($this->returned_quantity < $this->quantity) {
            $this->status = 'partially_returned';
        } else {
            $this->status = 'fully_returned';
        }
    }

    public function calculatePlannedTotalCost(): float
    {
        if (!$this->planned_rental_days) {
            return 0;
        }

        return $this->total_price * $this->planned_rental_days;
    }

    // Metody dla numeru seryjnych (jeśli są przechowywane jako JSON)
    public function getSerialNumbersArrayAttribute(): array
    {
        if (empty($this->serial_numbers)) {
            return [];
        }

        // Sprawdź czy to JSON czy zwykły string
        $decoded = json_decode($this->serial_numbers, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        // Jeśli nie JSON, podziel po przecinkach
        return array_map('trim', explode(',', $this->serial_numbers));
    }

    public function setSerialNumbersArray(array $serialNumbers): void
    {
        $this->serial_numbers = json_encode($serialNumbers);
    }

    public function addSerialNumber(string $serialNumber): void
    {
        $serialNumbers = $this->getSerialNumbersArrayAttribute();
        if (!in_array($serialNumber, $serialNumbers)) {
            $serialNumbers[] = $serialNumber;
            $this->setSerialNumbersArray($serialNumbers);
        }
    }

    public function removeSerialNumber(string $serialNumber): void
    {
        $serialNumbers = $this->getSerialNumbersArrayAttribute();
        $serialNumbers = array_filter($serialNumbers, fn($sn) => $sn !== $serialNumber);
        $this->setSerialNumbersArray(array_values($serialNumbers));
    }
}
