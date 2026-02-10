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
        Schema::create('policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ship_order_data_id')->constrained('ship_order_data')->cascadeOnDelete();
            $table->foreignId('operating_order_id')->constrained('operating_orders')->cascadeOnDelete();
            $table->string('policy_number')->unique();
            $table->double('covenant_amount')->nullable();
            $table->boolean('policy_type')->default(false);
            $table->dateTime('policy_aging_date')->nullable();
            $table->dateTime('policy_loading_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policies');
    }
};
