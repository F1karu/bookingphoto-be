<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            // ENUM price_type
            $table->enum('price_type', ['normal', 'premium', 'vip'])
                  ->default('normal')
                  ->after('duration');

            // base_price jadi numeric otomatis
            $table->integer('base_price')->default(0)->change();

            // total_price juga memastikan numeric
            $table->integer('total_price')->default(0)->change();
        });
    }

    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('price_type');
        });
    }
};
