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
        Schema::create('clearance_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->nullable()->constrained('ship_bookings')->onDelete('cascade');
            $table->string('clearance_type')->nullable();
            $table->string('customs_location')->nullable();
            $table->string('redirect_location')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clearance_data');
    }
};
