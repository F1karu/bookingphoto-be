<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('booking_addons', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_id')
                ->constrained('bookings')
                ->onDelete('cascade');

            $table->foreignId('addon_id')
                ->constrained('addons')
                ->onDelete('cascade');

            $table->integer('quantity');      // e.g., +2 hours
            $table->integer('subtotal');      // quantity * addon.price

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('booking_addons');
    }
};
