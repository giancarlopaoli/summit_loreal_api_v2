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
            $table->string('transfer_number',45)->nullable()->after('comission_amount');
            $table->integer('voucher_id')->nullable()->after('transfer_number');
            $table->timestamp('signed_at')->nullable()->after('voucher_id');
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
            $table->dropColumn('transfer_number');
            $table->dropColumn('voucher_id');
            $table->dropColumn('signed_at');
        });
    }
};
