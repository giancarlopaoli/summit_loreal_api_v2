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
        Schema::connection('mysql2')->create('vacations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Payroll::class)->constrained();
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('effective_days');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql2')->dropIfExists('vacations');
    }
};
