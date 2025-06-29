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
        Schema::create('rental_returns', function (Blueprint $table) {
            $table->id();
            
            // Podstawowe informacje
            $table->string('return_number')->unique(); // Numer protokołu zwrotu
            $table->date('return_date'); // Data zwrotu
            $table->text('notes')->nullable(); // Notatki
            
            // Powiązania - zwrot może być powiązany z wieloma wydaniami
            $table->json('related_issue_ids')->nullable(); // ID powiązanych wydań
            $table->json('related_rental_document_ids')->nullable(); // ID powiązanych umów
            
            // Dane klienta
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();
            
            // Transport
            $table->decimal('transport_cost', 10, 2)->default(0); // Koszt transportu zwrotu
            $table->boolean('transport_included')->default(false); // Czy transport uwzględniony
            $table->text('transport_notes')->nullable(); // Notatki do transportu
            
            // Lokalizacja
            $table->text('pickup_address')->nullable(); // Adres odbioru
            $table->string('pickup_contact_person')->nullable(); // Osoba kontaktowa
            $table->string('pickup_contact_phone')->nullable(); // Telefon kontaktowy
            
            // Status
            $table->enum('status', ['draft', 'returned', 'processed', 'cancelled'])
                  ->default('draft');
            
            // Podpisy i autoryzacja
            $table->string('returned_by')->nullable(); // Kto zwrócił
            $table->string('received_by')->nullable(); // Kto odebrał zwrot
            $table->timestamp('returned_at')->nullable(); // Kiedy zwrócono
            
            // Ocena stanu sprzętu
            $table->enum('equipment_condition', ['excellent', 'good', 'fair', 'poor', 'damaged'])
                  ->default('good');
            $table->text('condition_notes')->nullable(); // Notatki o stanie
            
            // Kary i dodatkowe opłaty
            $table->decimal('damage_fee', 10, 2)->default(0); // Opłata za uszkodzenia
            $table->decimal('late_fee', 10, 2)->default(0); // Opłata za opóźnienie
            $table->decimal('additional_fees', 10, 2)->default(0); // Dodatkowe opłaty
            $table->text('fees_description')->nullable(); // Opis opłat
            
            // Podsumowanie finansowe
            $table->decimal('total_rental_days', 8, 2)->default(0); // Łączna liczba dni wynajmu
            $table->decimal('total_rental_cost', 10, 2)->default(0); // Łączny koszt wynajmu
            $table->decimal('total_additional_costs', 10, 2)->default(0); // Łączne dodatkowe koszty
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_returns');
    }
};
