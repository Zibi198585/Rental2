<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::insert([
            [
                'name' => 'Podpora stropowa 10kN typ włoski 2,00-3,60m (ocynk)',
                'description' => 'Wytrzymała podpora stropowa, zakres 2,00-3,60m, ocynkowana.',
                'price_per_day' => 200, // 2,00 zł (w groszach)
            ],
            [
                'name' => 'Sztywna belka H20 2,9m',
                'description' => 'Belka drewniana H20, długość 2,9m.',
                'price_per_day' => 350, // 3,50 zł (w groszach)
            ],
            [
                'name' => 'Rusztowanie warszawskie',
                'description' => 'Modułowe rusztowanie warszawskie, wysokość 2m.',
                'price_per_day' => 500, // 5,00 zł (w groszach)
            ],
            [
                'name' => 'Agregat prądotwórczy Honda 3kW',
                'description' => 'Agregat prądotwórczy Honda, moc 3kW, cichy.',
                'price_per_day' => 2500, // 25,00 zł (w groszach)
            ],
        ]);
    }
}
