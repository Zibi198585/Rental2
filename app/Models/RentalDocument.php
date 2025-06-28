<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Casts\MoneyCast;

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
        'rental_days',

        'equipment_location',

        'deposit',

        // Delivery
        'delivery_method',
        'delivery_cost',
        'pickup_cost',


    ];

    protected $casts = [
        'delivery_cost' => MoneyCast::class,
        'pickup_cost'   => MoneyCast::class,
        'deposit'       => MoneyCast::class,
    ];

    protected $with = ['products'];

    public function getRentalPeriodInfoAttribute(): string
    {
        if (!$this->rental_date || !$this->expected_return_date) {
            return '';
        }

        $rentalDate = \Illuminate\Support\Carbon::parse($this->rental_date)->format('d.m.Y');
        $returnDate = \Illuminate\Support\Carbon::parse($this->expected_return_date)->format('d.m.Y');
        $now = \Illuminate\Support\Carbon::now()->startOfDay();
        $expectedReturn = \Illuminate\Support\Carbon::parse($this->expected_return_date)->startOfDay();

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
                ? \Illuminate\Support\Carbon::parse($this->real_return_date)->startOfDay()
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
        $date = $rentalDate ? \Illuminate\Support\Carbon::parse($rentalDate) : now();
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


}


