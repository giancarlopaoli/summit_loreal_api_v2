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
        Schema::connection('mysql2')->create('payroll_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Payroll::class)->constrained();
            $table->integer('month');
            $table->integer('year');
            $table->date('payment_date', 7,2);
            $table->string('payment_slip_url', 100)->nullable();
            $table->text('comments')->nullable();
            $table->string('account_number', 30);
            $table->string('cci_number', 35);
            $table->foreignIdFor(\App\Models\BankAccount::class);
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
        Schema::connection('mysql2')->dropIfExists('payroll_payments');
    }
};
