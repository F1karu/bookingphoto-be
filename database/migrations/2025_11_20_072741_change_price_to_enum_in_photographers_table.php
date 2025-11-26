<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photographers', function (Blueprint $table) {
            $table->enum('price_per_hour', ['normal', 'professional'])
                  ->default('normal')
                  ->change();
        });
    }

    public function down(): void
    {
        Schema::table('photographers', function (Blueprint $table) {
            // Kembalikan ke integer jika rollback
            $table->integer('price_per_hour')->change();
        });
    }
};

