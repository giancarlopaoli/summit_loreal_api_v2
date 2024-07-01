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
        Schema::connection('mysql2')->create('purchase_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Service::class)->constrained();
            $table->decimal('total_amount', 7,2);
            $table->decimal('total_igv', 7,2);
            $table->decimal('total_ipm', 7,2);
            $table->enum('type', ['Producto', 'Servicio']);
            $table->foreignIdFor(\App\Models\Currency::class);
            $table->decimal('exchange_rate', 5, 4)->nullable();
            $table->decimal('detraction_amount', 7,2)->nullable();
            $table->date('detraction_payment_date', 7,2)->nullable();
            $table->string('serie', 10);
            $table->string('number', 15);
            $table->date('issue_date', 7,2);
            $table->date('due_date', 7,2);
            $table->integer('service_month')->nullable();
            $table->integer('service_year')->nullable();
            $table->enum('status', ['Borrador', 'Pendiente pago', 'Pagado', 'Cancelado']);
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
        Schema::connection('mysql2')->dropIfExists('purchase_invoices');
    }
};
