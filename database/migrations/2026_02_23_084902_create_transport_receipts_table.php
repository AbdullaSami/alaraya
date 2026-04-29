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

            // Existing
            $table->decimal('army_scales')->nullable();                 // موازين الجيش
            $table->decimal('roads_and_bridges')->nullable();           // طرق وكباري
            $table->decimal('road_cards')->nullable();                  // ايصالات طريق
            $table->decimal('governorate_voucher')->nullable();         // بون محافظة
            $table->decimal('tips')->nullable();
            $table->decimal('official_receipts')->nullable();           // ايصالات عامة
            $table->decimal('overnight_leave')->nullable();
            $table->decimal('tarif_receipts')->nullable();
            $table->decimal('third_party_car_rental')->nullable();
            $table->decimal('customs_clearance')->nullable();           // ايصال جمرك
            $table->decimal('bill_of_lading_amendment')->nullable();
            $table->decimal('third_party_vehicle_leave')->nullable();
            $table->decimal('brokers')->nullable();

            // 🔥 New fields from your list
            $table->decimal('vgm')->nullable();                         // VGM
            $table->decimal('x_ray')->nullable();                       // X-RAY
            $table->decimal('data_entry')->nullable();                  // DATA ENTRY
            $table->decimal('yard_receipts')->nullable();               // ايصالات ساحة
            $table->decimal('port_authority_receipts')->nullable();     // ايصالات هيئة مواني
            $table->decimal('port_weight_fees')->nullable();            // علوم وزن ميناء
            $table->decimal('agency_receipts')->nullable();             // إيصالات توكيل
            $table->decimal('explosives_receipt')->nullable();          // ايصال مفرقعات
            $table->decimal('bascule_scale_receipt')->nullable();       // ايصال ميزان بيسكول
            $table->decimal('cashier_receipt')->nullable();             // ايصال كاشير
            $table->decimal('reweighing_receipt')->nullable();          // ايصال اعادة وزن
            $table->decimal('sina_marine_receipts')->nullable();        // ايصالات سينا مارين
            $table->decimal('tunnel_ferry_receipts')->nullable();       // ايصالات نفق ومعديه
            $table->decimal('container_repair_receipt')->nullable();    // ايصال اصلاح حاويات
            $table->decimal('port_receipts')->nullable();               // ايصالات ميناء
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
