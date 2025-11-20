<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_id')
                ->constrained('bookings')
                ->onDelete('cascade');

            $table->string('order_id');  // Midtrans order ID: BOOK-xxxx
            $table->integer('amount');
            $table->string('payment_method')->nullable();

            $table->enum('payment_status', ['pending', 'paid', 'failed'])
                ->default('pending');

            $table->string('transaction_status')->nullable(); // settlement, pending, expire, cancel
            $table->string('fraud_status')->nullable();

            $table->json('midtrans_payload')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
