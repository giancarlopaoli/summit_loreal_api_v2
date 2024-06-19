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
        Schema::connection('mysql2')->create('purchase_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\PurchaseInvoice::class)->constrained();
            $table->date('payment_date', 7,2);
            $table->enum('payment_method', ['Efectivo', 'Cheque','Transferencia bancaria','Reembolso']);
            $table->decimal('amount', 7,2);
            $table->foreignIdFor(\App\Models\Currency::class);
            $table->string('transfer_number', 50)->nullable();
            $table->string('comments', 200)->nullable();
            $table->enum('status', ['Ingresado', 'Pagado','Cancelado']);
            $table->foreignIdFor(\App\Models\BusinessBankAccount::class)->constrained();
            $table->foreignIdFor(\App\Models\SupplierBankAccount::class)->nullable()->constrained();
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
        Schema::connection('mysql2')->dropIfExists('purchase_payments');
    }
};
