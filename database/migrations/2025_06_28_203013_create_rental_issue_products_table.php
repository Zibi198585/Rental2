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
        Schema::create('rental_issue_products', function (Blueprint $table) {
            $table->id();
            
            // Relacje
            $table->foreignId('rental_issue_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            // Szczegóły produktu w wydaniu
            $table->integer('quantity')->default(1); // Ilość wydana
            $table->decimal('unit_price', 10, 2); // Cena jednostkowa za dzień
            $table->decimal('total_price', 10, 2); // Łączna cena za dzień (quantity * unit_price)
            
            // Szczegóły techniczne
            $table->text('technical_notes')->nullable(); // Notatki techniczne
            $table->string('serial_numbers')->nullable(); // Numery seryjne (JSON lub string)
            $table->text('condition_before')->nullable(); // Stan przed wydaniem
            
            // Daty planowane
            $table->date('planned_return_date')->nullable(); // Planowana data zwrotu
            $table->integer('planned_rental_days')->nullable(); // Planowana liczba dni
            
            // Status
            $table->enum('status', ['issued', 'partially_returned', 'fully_returned'])
                  ->default('issued');
            $table->integer('returned_quantity')->default(0); // Ile już zwrócono
            
            $table->timestamps();
            
            // Indeksy
            $table->unique(['rental_issue_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_issue_products');
    }
};
