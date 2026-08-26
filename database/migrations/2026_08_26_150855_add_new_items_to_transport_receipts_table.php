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
        Schema::table('transport_receipts', function (Blueprint $table) {
            $table->decimal('x_ray_leave')->nullable();
            $table->decimal('allocation')->nullable();
            $table->decimal('guarantee')->nullable();
            $table->decimal('form')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transport_receipts', function (Blueprint $table) {
            $table->dropColumn(['x_ray_leave', 'allocation', 'guarantee', 'form']);
        });
    }
};
