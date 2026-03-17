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
        Schema::create('transport_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ship_order_id')->constrained('ship_order_data')->cascadeOnDelete();
            $table->foreignId('policy_id')->constrained('policies')->cascadeOnDelete();
            $table->decimal('army_scales')->nullable();
            $table->decimal('roads_and_bridges')->nullable();
            $table->decimal('road_cards')->nullable();
            $table->decimal('governorate_voucher')->nullable();
            $table->decimal('tips')->nullable();
            $table->decimal('official_receipts')->nullable();
            $table->decimal('overnight_leave')->nullable();
            $table->decimal('tarif_receipts')->nullable();
            $table->decimal('third_party_car_rental')->nullable();
            $table->decimal('customs_clearance')->nullable();
            $table->decimal('bill_of_lading_amendment')->nullable();
            $table->decimal('third_party_vehicle_leave')->nullable();
            $table->decimal('brokers')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transport_receipts');
    }
};
