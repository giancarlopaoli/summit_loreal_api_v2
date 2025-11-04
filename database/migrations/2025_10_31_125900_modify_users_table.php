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
        Schema::table('users', function (Blueprint $table) {
            $table->string('car1_en',50)->nullable()->after('car3');
            $table->string('car2_en',50)->nullable()->after('car1_en');
            $table->string('car3_en',50)->nullable()->after('car2_en');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('car1_en');
            $table->dropColumn('car2_en');
            $table->dropColumn('car3_en');
            //
        });
    }
};
