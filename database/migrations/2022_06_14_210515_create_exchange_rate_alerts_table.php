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
        Schema::create('exchange_rate_alerts', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['Compra', 'Venta']);
            $table->foreignIdFor(\App\Models\Client::class)->constrained();
            $table->decimal('exchange_rate', 7,6 );
            $table->string('email', 255)->nullable();
            $table->string('phone', 255)->nullable();
            $table->enum('status', ['Activo', 'Eliminado', 'Atendido']);
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
        Schema::dropIfExists('exchange_rate_alerts');
    }
};
