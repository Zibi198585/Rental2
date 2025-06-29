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
        Schema::create('rental_issues', function (Blueprint $table) {
            $table->id();
            
            // Podstawowe informacje
            $table->string('issue_number')->unique(); // Numer protokołu wydania
            $table->foreignId('rental_document_id')->nullable()->constrained()->onDelete('set null'); // Może być niezwiązane
            $table->date('issue_date'); // Data wydania
            $table->text('notes')->nullable(); // Notatki
            
            // Dane klienta (gdy wydanie bez umowy)
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();
            $table->text('customer_address')->nullable();
            
            // Transport
            $table->decimal('transport_cost', 10, 2)->default(0); // Koszt transportu
            $table->boolean('transport_included')->default(false); // Czy transport uwzględniony
            $table->text('transport_notes')->nullable(); // Notatki do transportu
            
            // Lokalizacja
            $table->text('delivery_address')->nullable(); // Adres dostawy
            $table->string('delivery_contact_person')->nullable(); // Osoba kontaktowa
            $table->string('delivery_contact_phone')->nullable(); // Telefon kontaktowy
            
            // Status
            $table->enum('status', ['draft', 'issued', 'partially_returned', 'fully_returned', 'cancelled'])
                  ->default('draft');
            
            // Podpisy i autoryzacja
            $table->string('issued_by')->nullable(); // Kto wydał
            $table->string('received_by')->nullable(); // Kto odebrał
            $table->timestamp('issued_at')->nullable(); // Kiedy wydano
            
            // Podsumowanie finansowe
            $table->decimal('total_daily_cost', 10, 2)->default(0); // Koszt dzienny
            $table->decimal('estimated_total_cost', 10, 2)->default(0); // Szacowany koszt całkowity
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_issues');
    }
};
