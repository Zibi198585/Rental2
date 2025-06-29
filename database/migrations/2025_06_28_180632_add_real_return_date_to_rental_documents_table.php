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
            $table->date('real_return_date')->nullable()->after('expected_return_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rental_documents', function (Blueprint $table) {
            $table->dropColumn('real_return_date');
        });
    }
};
