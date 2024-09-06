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
        Schema::connection('mysql2')->create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('description', 200)->nullable();
            $table->foreignIdFor(\App\Models\Client::class);
            $table->decimal('amount', 7,2);
            $table->decimal('igv', 7,2);
            $table->foreignIdFor(\App\Models\Currency::class);
            $table->decimal('exchange_rate', 5, 4)->nullable();
            $table->decimal('detraction_amount', 7,2)->nullable();
            $table->date('detraction_payment_date', 7,2)->nullable();
            $table->string('detraction_url', 150)->nullable();
            $table->string('invoice_serie', 10);
            $table->string('invoice_number', 15);
            $table->string('invoice_url', 255);
            $table->date('invoice_issue_date', 7,2);
            $table->date('invoice_expire_date', 7,2)->nullable();
            $table->enum('status', ['Aceptada', 'Anulada']);
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
        Schema::connection('mysql2')->dropIfExists('sales');
    }
};
