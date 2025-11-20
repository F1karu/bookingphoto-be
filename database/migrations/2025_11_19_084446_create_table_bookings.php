<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            // User info
            $table->string('user_name');
            $table->string('user_phone');

            // Session info
            $table->date('date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('duration')->nullable(); // in minutes or hours

            // Pricing
            $table->integer('base_price');
            $table->integer('total_price');

            // Notes
            $table->text('note')->nullable();

            // Photographer assigned later by admin
            $table->foreignId('photographer_id')
                ->nullable()
                ->constrained('photographers')
                ->nullOnDelete();

            // Booking status
            $table->enum('booking_status', [
                'PENDING',
                'WAITING_PAYMENT',
                'PAID',
                'SCHEDULED',
                'IN_PROGRESS',
                'COMPLETED',
                'CANCELED'
            ])->default('PENDING');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bookings');
    }
};
