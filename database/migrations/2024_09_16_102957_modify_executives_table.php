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
        Schema::table('executives', function (Blueprint $table) {
            $table->enum('status', ['Activo', 'Inactivo'])->default('Activo')->after('years');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('executives', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
