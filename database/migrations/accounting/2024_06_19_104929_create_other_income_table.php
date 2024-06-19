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
        Schema::connection('mysql2')->create('other_income', function (Blueprint $table) {
            $table->id();
            $table->date('income_date', 7,2);
            $table->decimal('amount', 7,2);
            $table->foreignIdFor(\App\Models\Currency::class);
            $table->enum('type', ['Reembolso', 'Préstamo', 'Aporte socio']);
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
        Schema::connection('mysql2')->dropIfExists('other_income');
    }
};
