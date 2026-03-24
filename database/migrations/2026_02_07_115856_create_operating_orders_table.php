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
            $table->string('cause_note')->nullable(); // Note for the cause of not being an operating order
            $table->text('operating_order_image')->nullable();
            $table->text('operating_order_location')->nullable();
            $table->text('operating_order_mail_image')->nullable();
            // Torrents data
            $table->boolean('is_torrents')->default(false);
            $table->string('torrents_cause_note')->nullable(); // Note for the cause of being a torrents
            $table->text('torrents_image')->nullable();
            $table->dateTime('pull_torrents_date')->nullable();
            $table->dateTime('load_torrents_date')->nullable();
            // Release and assignment data
            $table->text('release_and_assignment_image')->nullable();
            $table->text('release_and_assignment_requirements')->nullable();

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
