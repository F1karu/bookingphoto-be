<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeletedAtToAddonsTable extends Migration
{
    public function up()
    {
        Schema::table('addons', function (Blueprint $table) {
            $table->softDeletes(); // otomatis membuat deleted_at nullable
        });
    }

    public function down()
    {
        Schema::table('addons', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}
