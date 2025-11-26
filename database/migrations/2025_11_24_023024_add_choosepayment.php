<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            // Hapus kolom lama kalau ada enum sebelumnya
            $table->dropColumn(['payment_method']);

            // Buat ulang payment_method sebagai enum baru
            $table->enum('payment_method', ['MANUAL', 'MIDTRANS'])->default('MANUAL');
            $table->string('channel')->nullable();
            $table->string('snap_token')->nullable();
            $table->string('redirect_url')->nullable();
            $table->json('midtrans_payload')->nullable();
            $table->string('proof')->nullable();
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'channel',
                'snap_token',
                'redirect_url',
                'midtrans_payload',
                'proof',
            ]);
        });
    }
};
