<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rental_return_products', function (Blueprint $table) {
            $table->id();
            
            // Relacje
            $table->foreignId('rental_return_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            // Powiązania z wydaniami
            $table->json('related_issue_product_ids')->nullable(); // ID z rental_issue_products
            
            // Szczegóły produktu w zwrocie
            $table->integer('quantity')->default(1); // Ilość zwracana
            $table->integer('actual_rental_days')->nullable(); // Faktyczna liczba dni wynajmu
            $table->decimal('daily_rate', 10, 2); // Stawka dzienna
            $table->decimal('total_rental_cost', 10, 2)->default(0); // Łączny koszt wynajmu
            
            // Stan sprzętu
            $table->enum('condition', ['excellent', 'good', 'fair', 'poor', 'damaged'])
                  ->default('good');
            $table->text('condition_notes')->nullable(); // Notatki o stanie
            $table->string('returned_serial_numbers')->nullable(); // Zwracane numery seryjne
            
            // Dodatkowe opłaty
            $table->decimal('damage_cost', 10, 2)->default(0); // Koszt uszkodzeń
            $table->decimal('late_fee', 10, 2)->default(0); // Opłata za opóźnienie
            $table->decimal('cleaning_fee', 10, 2)->default(0); // Opłata za czyszczenie
            $table->decimal('other_fees', 10, 2)->default(0); // Inne opłaty
            $table->text('fees_description')->nullable(); // Opis opłat
            
            // Daty
            $table->date('issue_date')->nullable(); // Data wydania (dla obliczenia dni)
            $table->date('return_date'); // Data zwrotu
            
            // Status
            $table->enum('status', ['returned', 'inspected', 'processed', 'damaged'])
                  ->default('returned');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_return_products');
    }
};
