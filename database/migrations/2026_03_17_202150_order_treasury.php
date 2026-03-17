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
        Schema::table('order_treasury', function (Blueprint $table) {
            $table->unsignedBigInteger('treasury_id')->nullable()->after('clearance_data');
            $table->foreign('treasury_id')->references('id')->on('treasuries')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
