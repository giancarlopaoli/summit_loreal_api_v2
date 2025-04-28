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
        Schema::connection('mysql2')->create('accountable_exchange_rate', function (Blueprint $table) {
            $table->date('date', 7,2)->unique();
            $table->decimal('sunat_compra', 5, 4)->nullable();
            $table->decimal('sunat_venta', 5, 4)->nullable();
            $table->decimal('sbs_compra', 5, 4)->nullable();
            $table->decimal('sbs_venta', 5, 4)->nullable();
            $table->timestamps();
            $table->primary('date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql2')->dropIfExists('accountable_exchange_rate');
    }
};
