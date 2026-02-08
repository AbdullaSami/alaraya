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
        Schema::create('torrent_containers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operating_order_id')->constrained('operating_orders')->onDelete('cascade');
            $table->foreignId('container_id')->constrained('ship_containers_details')->onDelete('cascade');
            $table->string('torrent_number');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('torrent_containers');
    }
};
