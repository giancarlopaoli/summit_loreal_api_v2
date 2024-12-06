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
        Schema::table('ranges', function (Blueprint $table) {
            $table->dropColumn('comission_open');
            $table->dropColumn('comission_close');

            $table->decimal('comission_open_sell', 7, 2)->after('max_range');
            $table->decimal('comission_open_buy', 7, 2)->after('comission_open_sell');
            $table->decimal('comission_close_sell', 7, 2)->after('comission_open_buy');
            $table->decimal('comission_close_buy', 7, 2)->after('comission_close_sell');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ranges', function (Blueprint $table) {
            $table->decimal('comission_open', 7, 2)->after('max_range');
            $table->decimal('comission_close', 7, 2)->after('comission_open');
            
            $table->dropColumn('comission_open_sell');
            $table->dropColumn('comission_open_buy');
            $table->dropColumn('comission_close_sell');
            $table->dropColumn('comission_close_buy');
        });
    }
};
