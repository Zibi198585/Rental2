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
        Schema::table('rental_documents', function (Blueprint $table) {
            // Dodaj nowe kolumny podsumowania (wszystko w groszach)
            $table->integer('summary_products_per_day')->nullable()->after('vat_rate');
            $table->integer('summary_products_total')->nullable()->after('summary_products_per_day');
            $table->integer('summary_delivery')->nullable()->after('summary_products_total');
            $table->integer('summary_deposit')->nullable()->after('summary_delivery');
            $table->integer('summary_total')->nullable()->after('summary_deposit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rental_documents', function (Blueprint $table) {
            $table->dropColumn([
                'summary_products_per_day',
                'summary_products_total',
                'summary_delivery',
                'summary_deposit',
                'summary_total',
            ]);
        });
    }
};
