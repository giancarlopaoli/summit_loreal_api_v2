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
        Schema::table('bank_account_operation', function (Blueprint $table) {
            $table->integer('escrow_account_operation_id')->nullable()->after('signed_at');
            $table->timestamp('deposit_at')->nullable()->after('escrow_account_operation_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bank_account_operation', function (Blueprint $table) {
            $table->dropColumn('escrow_account_operation_id');
            $table->dropColumn('deposit_at');
        });
    }
};
