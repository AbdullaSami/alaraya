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
        Schema::create('ship_containers_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('policy_id')->nullable()->constrained('ship_policies')->onDelete('cascade');
            $table->foreignId('booking_id')->nullable()->constrained('ship_bookings')->onDelete('cascade');
            $table->string('container_number')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ship_containers_details');
    }
};
