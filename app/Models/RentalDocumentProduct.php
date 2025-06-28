<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Casts\MoneyCast;

class RentalDocumentProduct extends Model
{
    protected $fillable = [
        'rental_document_id',
        'product_name',
        'quantity',
        'price_per_day',
        'total_price',
    ];

    protected $casts = [
        'price_per_day' => MoneyCast::class,
        'total_price'   => MoneyCast::class,
    ];

    protected $with = ['product'];

    public function rentalDocument()
    {
        return $this->belongsTo(RentalDocument::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
