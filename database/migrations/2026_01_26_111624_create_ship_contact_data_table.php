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
        Schema::create('ship_contact_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ship_order_data_id')->constrained('ship_order_data')->onDelete('cascade');
            $table->string('contact_loading_name');
            $table->string('contact_loading_number');
            $table->string('contact_customs_officer_name');
            $table->string('contact_customs_officer_number');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ship_contact_data');
    }
};
