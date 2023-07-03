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
        Schema::create('executives_comissions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Executive::class)->constrained();
            $table->foreignIdFor(\App\Models\Client::class)->constrained();
            $table->decimal('comission', 5,  4);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
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
        Schema::dropIfExists('executives_comissions');
    }
};
