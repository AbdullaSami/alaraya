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
        Schema::create('operating_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ship_order_data_id')->constrained('ship_order_data')->onDelete('cascade');
            $table->boolean('is_operating_order')->default(false);
            $table->string('requirement_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operating_orders');
    }
};
