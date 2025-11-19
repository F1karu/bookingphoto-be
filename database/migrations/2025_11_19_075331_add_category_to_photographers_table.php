<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('photographers', function (Blueprint $table) {
        $table->enum('category', [
            'wedding',
            'portrait',
            'event',
            'newborn',
            'product',
            'family'
        ])->default('portrait')->after('city_id');
    });
}

public function down()
{
    Schema::table('photographers', function (Blueprint $table) {
        $table->dropColumn('category');
    });
}


    /**
     * Reverse the migrations.
     */

};
