<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('escrow_account_operation', function (Blueprint $table) {
            $table->timestamp('deposit_at')->nullable()->after('voucher_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('escrow_account_operation', function (Blueprint $table) {
            $table->dropColumn('deposit_at');
        });
    }
};
