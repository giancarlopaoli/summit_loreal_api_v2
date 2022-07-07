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
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Client::class)->constrained();
            $table->string('alias', 50);
            $table->foreignIdFor(\App\Models\Bank::class)->constrained();
            $table->string('account_number', 25);
            $table->string('cci_number', 25);
            $table->boolean('active');
            $table->string('comments', 150);
            $table->foreignIdFor(\App\Models\AccountType::class)->constrained();
            $table->foreignIdFor(\App\Models\Currency::class)->constrained();
            $table->foreignId('updated_by')->constrained('users');
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
        Schema::dropIfExists('bank_accounts');
    }
};
