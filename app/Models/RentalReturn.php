<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class RentalReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_number',
        'return_date',
        'notes',
        'related_issue_ids',
        'related_rental_document_ids',
        'customer_name',
        'customer_phone',
        'customer_email',
        'transport_cost',
        'transport_included',
        'transport_notes',
        'pickup_address',
        'pickup_contact_person',
        'pickup_contact_phone',
        'status',
        'returned_by',
        'received_by',
        'returned_at',
        'equipment_condition',
        'condition_notes',
        'damage_fee',
        'late_fee',
        'additional_fees',
        'fees_description',
        'total_rental_days',
        'total_rental_cost',
        'total_additional_costs',
    ];

    protected $casts = [
        'return_date' => 'date',
        'returned_at' => 'datetime',
        'related_issue_ids' => 'array',
        'related_rental_document_ids' => 'array',
        'transport_cost' => MoneyCast::class,
        'damage_fee' => MoneyCast::class,
        'late_fee' => MoneyCast::class,
        'additional_fees' => MoneyCast::class,
        'total_rental_cost' => MoneyCast::class,
        'total_additional_costs' => MoneyCast::class,
        'total_rental_days' => 'decimal:2',
        'transport_included' => 'boolean',
    ];

    // Relacje
    public function products(): HasMany
    {
        return $this->hasMany(RentalReturnProduct::class);
    }

    public function relatedIssues()
    {
        if (empty($this->related_issue_ids)) {
            return collect();
        }
        
        return RentalIssue::whereIn('id', $this->related_issue_ids)->get();
    }

    public function relatedRentalDocuments()
    {
        if (empty($this->related_rental_document_ids)) {
            return collect();
        }
        
        return RentalDocument::whereIn('id', $this->related_rental_document_ids)->get();
    }

    // Automatyczne generowanie numeru zwrotu
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($return) {
            if (empty($return->return_number)) {
                $return->return_number = self::generateReturnNumber();
            }
        });
    }

    public static function generateReturnNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        
        $lastReturn = self::whereYear('created_at', $year)
                          ->whereMonth('created_at', $month)  
                          ->orderBy('id', 'desc')
                          ->first();

        $nextNumber = $lastReturn ? 
            intval(substr($lastReturn->return_number, -4)) + 1 : 1;

        return "ZWR/{$year}/{$month}/" . str_pad((string)$nextNumber, 4, '0', STR_PAD_LEFT);
    }

    // Akcesory i mutatory
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Szkic',
            'returned' => 'Zwrócone',
            'processed' => 'Przetworzone',
            'cancelled' => 'Anulowane',
            default => $this->status,
        };
    }

    public function getEquipmentConditionLabelAttribute(): string
    {
        return match($this->equipment_condition) {
            'excellent' => 'Doskonały',
            'good' => 'Dobry',
            'fair' => 'Przeciętny',
            'poor' => 'Słaby',
            'damaged' => 'Uszkodzony',
            default => $this->equipment_condition,
        };
    }

    public function getHasAdditionalFeesAttribute(): bool
    {
        return $this->damage_fee > 0 || $this->late_fee > 0 || $this->additional_fees > 0;
    }

    public function getTotalFeesAttribute(): float
    {
        return $this->damage_fee + $this->late_fee + $this->additional_fees;
    }

    public function getGrandTotalAttribute(): float
    {
        return $this->total_rental_cost + $this->total_additional_costs + $this->transport_cost;
    }

    // Obliczenia
    public function calculateTotalRentalCost(): float
    {
        return $this->products->sum('total_rental_cost');
    }

    public function calculateTotalAdditionalCosts(): float
    {
        return $this->products->sum(function ($product) {
            return $product->damage_cost + $product->late_fee + $product->cleaning_fee + $product->other_fees;
        });
    }

    public function calculateTotalRentalDays(): float
    {
        $totalDays = $this->products->sum('actual_rental_days');
        $productCount = $this->products->count();
        
        return $productCount > 0 ? $totalDays / $productCount : 0;
    }

    // Metody pomocnicze
    public function addRelatedIssue(int $issueId): void
    {
        $relatedIds = $this->related_issue_ids ?? [];
        
        if (!in_array($issueId, $relatedIds)) {
            $relatedIds[] = $issueId;
            $this->related_issue_ids = $relatedIds;
            $this->save();
        }
    }

    public function addRelatedRentalDocument(int $documentId): void
    {
        $relatedIds = $this->related_rental_document_ids ?? [];
        
        if (!in_array($documentId, $relatedIds)) {
            $relatedIds[] = $documentId;
            $this->related_rental_document_ids = $relatedIds;
            $this->save();
        }
    }

    public function removeRelatedIssue(int $issueId): void
    {
        $relatedIds = $this->related_issue_ids ?? [];
        $relatedIds = array_filter($relatedIds, fn($id) => $id !== $issueId);
        
        $this->related_issue_ids = array_values($relatedIds);
        $this->save();
    }

    // Finalizacja zwrotu
    public function finalizeReturn(): void
    {
        if ($this->status !== 'draft') {
            return;
        }

        $this->total_rental_cost = $this->calculateTotalRentalCost();
        $this->total_additional_costs = $this->calculateTotalAdditionalCosts();
        $this->total_rental_days = $this->calculateTotalRentalDays();
        $this->status = 'returned';
        $this->returned_at = now();

        $this->save();

        // Aktualizuj statusy powiązanych wydań
        $this->updateRelatedIssuesStatus();
    }

    private function updateRelatedIssuesStatus(): void
    {
        foreach ($this->relatedIssues() as $issue) {
            $issue->updateStatus();
        }
    }

    // Scopes
    public function scopeProcessed(Builder $query): Builder
    {
        return $query->where('status', 'processed');
    }

    public function scopeWithDamages(Builder $query): Builder
    {
        return $query->where('damage_fee', '>', 0);
    }

    public function scopeWithLateFees(Builder $query): Builder
    {
        return $query->where('late_fee', '>', 0);
    }
}
