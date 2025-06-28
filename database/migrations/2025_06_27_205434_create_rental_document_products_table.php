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
        Schema::create('rental_document_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_document_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete(); // <-- dodaj to
            $table->string('product_name');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('price_per_day', 8, 2)->default(0); // w złotych
            $table->decimal('total_price', 8, 2)->default(0); // w złotych (price_per_day * quantity)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_document_products');
    }
};
