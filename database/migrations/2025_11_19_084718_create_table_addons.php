<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('addons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('price');
            $table->integer('auto_enhanced_photo')->default(0);
            $table->enum('type', ['hour', 'photo']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('addons');
    }
};
