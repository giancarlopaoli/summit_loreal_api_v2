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
        Schema::connection('mysql2')->create('supplier_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Supplier::class)->constrained();
            $table->foreignIdFor(\App\Models\Bank::class);
            $table->string('account_number', 30);
            $table->string('cci_number', 35);
            $table->foreignIdFor(\App\Models\Currency::class);
            $table->foreignIdFor(\App\Models\AccountType::class);
            $table->boolean('main')->default(false);
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
        Schema::connection('mysql2')->dropIfExists('supplier_bank_accounts');
    }
};
