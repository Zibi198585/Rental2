<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Casts\MoneyCast;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price_per_day',
        'image',
    ];

    protected $casts = [
        'price_per_day' => MoneyCast::class,
    ];
}
