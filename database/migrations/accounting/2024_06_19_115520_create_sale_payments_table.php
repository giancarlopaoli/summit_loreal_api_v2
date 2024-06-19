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
        Schema::connection('mysql2')->create('sale_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Sale::class)->constrained();
            $table->date('payment_date', 7,2);
            $table->decimal('amount', 7,2);
            $table->foreignIdFor(\App\Models\Currency::class);
            $table->string('transfer_number', 50);
            $table->string('comments', 200)->nullable();
            $table->enum('status', ['Ingresado', 'Pagado','Cancelado']);
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
        Schema::connection('mysql2')->dropIfExists('sale_payments');
    }
};
