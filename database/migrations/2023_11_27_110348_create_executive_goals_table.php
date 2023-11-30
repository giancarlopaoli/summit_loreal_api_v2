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
        Schema::create('executive_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Executive::class)->constrained();
            $table->integer('month');
            $table->integer('year');
            $table->double('goal');
            $table->double('daily_goal');
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
        Schema::dropIfExists('executive_goals');
    }
};
