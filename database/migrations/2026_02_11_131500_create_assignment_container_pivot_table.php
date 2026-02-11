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
        Schema::create('assignment_container_pivot', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_driver_assignment_id')->constrained('vehicle_driver_assignments')->cascadeOnDelete();
            $table->foreignId('ship_container_id')->constrained('ship_containers_details')->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['vehicle_driver_assignment_id', 'ship_container_id'], 'assignment_container_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_container_pivot');
    }
};
