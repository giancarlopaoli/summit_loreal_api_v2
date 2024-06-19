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
        Schema::connection('mysql2')->create('other_expenses', function (Blueprint $table) {
            $table->id();
            $table->date('expense_date', 7,2);
            $table->decimal('amount', 7,2);
            $table->foreignIdFor(\App\Models\Currency::class);
            $table->decimal('exchange_rate', 5, 4)->nullable();
            $table->enum('type', ['ITF', 'Comisiones bancarias', 'Intereses']);
            $table->string('comments', 200)->nullable();
            $table->foreignIdFor(\App\Models\BusinessBankAccount::class)->constrained();
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
        Schema::connection('mysql2')->dropIfExists('other_expenses');
    }
};
