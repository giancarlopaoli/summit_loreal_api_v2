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
        Schema::create('bank_account_operation', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\BankAccount::class)->constrained();
            $table->foreignIdFor(\App\Models\Operation::class)->constrained();
            $table->decimal('amount', 11, 2);
            $table->decimal('comission_amount', 8, 2);
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
        Schema::dropIfExists('bank_account_operation');
    }
};
