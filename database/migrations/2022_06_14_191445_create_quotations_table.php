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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\User::class)->constrained();
            $table->foreignIdFor(\App\Models\Client::class)->constrained();
            $table->enum('type', ['Compra', 'Venta', 'Interbancario']);
            $table->decimal('ammount', 11, 2);
            $table->decimal('exchange_rate', 7, 6);
            $table->decimal('comission_spread', 7, 2);
            $table->decimal('comission_ammount', 7, 2);
            $table->decimal('igv', 7, 2);
            $table->decimal('spread', 7, 2);
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
        Schema::dropIfExists('quotations');
    }
};
