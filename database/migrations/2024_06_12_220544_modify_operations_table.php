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
        Schema::table('operations', function (Blueprint $table) {
            $table->string('unaffected_invoice_serie',4)->nullable()->after('invoice_url');
            $table->string('unaffected_invoice_number',10)->nullable()->after('unaffected_invoice_serie');
            $table->string('unaffected_invoice_url',255)->nullable()->after('unaffected_invoice_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('operations', function (Blueprint $table) {
            $table->dropColumn('unaffected_invoice_serie');
            $table->dropColumn('unaffected_invoice_number');
            $table->dropColumn('unaffected_invoice_url');
            //
        });
    }
};
