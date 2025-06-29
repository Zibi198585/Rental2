<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class RentalIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        'issue_number',
        'rental_document_id',
        'issue_date',
        'notes',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'transport_cost',
        'transport_included',
        'transport_notes',
        'delivery_address',
        'delivery_contact_person',
        'delivery_contact_phone',
        'status',
        'issued_by',
        'received_by',
        'issued_at',
        'total_daily_cost',
        'estimated_total_cost',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'issued_at' => 'datetime',
        'transport_cost' => MoneyCast::class,
        'total_daily_cost' => MoneyCast::class,
        'estimated_total_cost' => MoneyCast::class,
        'transport_included' => 'boolean',
    ];

    // Relacje
    public function rentalDocument(): BelongsTo
    {
        return $this->belongsTo(RentalDocument::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(RentalIssueProduct::class);
    }

    // Automatyczne generowanie numeru wydania
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($issue) {
            if (empty($issue->issue_number)) {
                $issue->issue_number = self::generateIssueNumber();
            }
        });
    }

    public static function generateIssueNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        
        $lastIssue = self::whereYear('created_at', $year)
                         ->whereMonth('created_at', $month)
                         ->orderBy('id', 'desc')
                         ->first();

        $nextNumber = $lastIssue ? 
            intval(substr($lastIssue->issue_number, -4)) + 1 : 1;

        return "WYD/{$year}/{$month}/" . str_pad((string)$nextNumber, 4, '0', STR_PAD_LEFT);
    }

    // Akcesory i mutatory
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Szkic',
            'issued' => 'Wydane',
            'partially_returned' => 'Częściowo zwrócone',
            'fully_returned' => 'Całkowicie zwrócone',
            'cancelled' => 'Anulowane',
            default => $this->status,
        };
    }

    public function getIsFromRentalDocumentAttribute(): bool
    {
        return !is_null($this->rental_document_id);
    }

    public function getCustomerDisplayNameAttribute(): string
    {
        if ($this->rentalDocument && $this->rentalDocument->contractor_full_name) {
            return $this->rentalDocument->contractor_full_name;
        }
        
        return $this->customer_name ?? 'Nieznany klient';
    }

    // Obliczenia
    public function calculateTotalDailyCost(): float
    {
        return $this->products->sum(function ($product) {
            return $product->total_price;
        });
    }

    public function calculateEstimatedTotalCost(?int $days = null): float
    {
        $dailyCost = $this->calculateTotalDailyCost();
        $estimatedDays = $days ?? $this->getAveragePlannedDays();
        
        return $dailyCost * $estimatedDays + $this->transport_cost;
    }

    public function getAveragePlannedDays(): int
    {
        $totalDays = $this->products->sum('planned_rental_days');
        $productCount = $this->products->count();
        
        return $productCount > 0 ? intval($totalDays / $productCount) : 1;
    }

    // Sprawdzanie statusu
    public function updateStatus(): void
    {
        if ($this->status === 'cancelled' || $this->status === 'draft') {
            return;
        }

        $totalQuantity = $this->products->sum('quantity');
        $returnedQuantity = $this->products->sum('returned_quantity');

        if ($returnedQuantity === 0) {
            $this->status = 'issued';
        } elseif ($returnedQuantity < $totalQuantity) {
            $this->status = 'partially_returned';
        } else {
            $this->status = 'fully_returned';
        }

        $this->save();
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['issued', 'partially_returned']);
    }

    public function scopeFromRentalDocument(Builder $query): Builder
    {
        return $query->whereNotNull('rental_document_id');
    }

    public function scopeStandalone(Builder $query): Builder
    {
        return $query->whereNull('rental_document_id');
    }
}
