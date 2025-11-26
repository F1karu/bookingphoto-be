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
    Schema::table('payments', function (Blueprint $table) {
        $table->timestamp('expires_at')->nullable()->after('payment_status');
        $table->timestamp('auto_failed_at')->nullable()->after('expires_at');
    });
}

public function down()
{
    Schema::table('payments', function (Blueprint $table) {
        $table->dropColumn(['expires_at', 'auto_failed_at']);
    });
}

};
