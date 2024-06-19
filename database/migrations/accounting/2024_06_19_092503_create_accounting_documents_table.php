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
        Schema::connection('mysql2')->create('accounting_documents', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->nullable();
            $table->foreignIdFor(\App\Models\PurchaseInvoice::class)->nullable()->constrained();
            $table->enum('type', ['Invoice', 'Payment']);
            $table->foreignIdFor(\App\Models\PurchasePayment::class)->nullable()->constrained();
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
        Schema::connection('mysql2')->dropIfExists('accounting_documents');
    }
};
