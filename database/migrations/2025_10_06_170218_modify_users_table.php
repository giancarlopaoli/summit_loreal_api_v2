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
            $table->string('car1',4)->nullable()->after('image');
            $table->string('car2',10)->nullable()->after('car1');
            $table->string('car3',255)->nullable()->after('car2');
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
            $table->dropColumn('car1');
            $table->dropColumn('car2');
            $table->dropColumn('car3');
            //
        });
    }
};
