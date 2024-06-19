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
        Schema::connection('mysql2')->create('business_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Bank::class);
            $table->string('alias', 200)->nullable();
            $table->string('account_number', 30);
            $table->string('cci_number', 35)->nullable();
            $table->string('comments', 150)->nullable();
            $table->foreignIdFor(\App\Models\AccountType::class);
            $table->foreignIdFor(\App\Models\Currency::class);
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
        Schema::connection('mysql2')->dropIfExists('business_bank_accounts');
    }
};
