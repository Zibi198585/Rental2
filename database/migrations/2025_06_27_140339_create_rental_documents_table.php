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
        Schema::create('rental_documents', function (Blueprint $table) {
            $table->id();

            $table->string('agreement_number')->nullable();

            $table->enum('status', ['draft','rented','partially_returned','scheduled_return','returned',])->default('draft');

            $table->string('city')->default('Wyry');
            $table->string('contractor_full_name');

            // Address
            $table->string('address_street')->nullable();
            $table->string('address_building_number')->nullable();
            $table->string('address_apartment_number')->nullable();
            $table->string('address_postal_code')->nullable();
            $table->string('address_city')->nullable();
            $table->string('address_voivodeship')->nullable();
            $table->string('address_country')->nullable();

            // Document type
            $table->enum('document_type', [
                'identity_card',
                'passport',
                'driving_license',
                'other'
            ])->default('identity_card');
            $table->string('other_document')->nullable();
            $table->string('document_number')->nullable();
            $table->string('pesel', 11)->nullable(); // PESEL
            $table->string('nip', 10)->nullable(); // NIP

            // Contact
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();

            // Dates and rental info
            $table->date('rental_date')->nullable();
            $table->date('expected_return_date')->nullable();
            $table->unsignedInteger('rental_days')->nullable();

            $table->string('equipment_location')->nullable();

            // Delivery
            $table->enum('delivery_method', [
                'self_pickup',
                'delivery_to_customer'
            ])->default('self_pickup');
            $table->integer('delivery_cost')->nullable(); // in cents
            $table->integer('pickup_cost')->nullable(); // in cents

            $table->integer('deposit')->nullable(); // in cents

            // Podsumowania (wszystko w groszach)
            $table->integer('summary_products_total_per_day')->nullable();
            $table->integer('summary_products_total_period')->nullable();
            $table->integer('summary_delivery_total_period')->nullable();
            $table->integer('summary_net_period')->nullable();
            $table->integer('summary_vat_period')->nullable();
            $table->integer('summary_gross_period')->nullable();
            $table->integer('vat_rate')->default(23); // Stawka VAT, domyślnie 23%

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_documents');
    }
};

