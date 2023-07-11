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
            $table->string('alias', 200);
            $table->foreignIdFor(\App\Models\Bank::class)->constrained();
            $table->string('account_number', 25);
            $table->string('cci_number', 25)->nullable();
            $table->boolean('main')->default(false);
            $table->foreignIdFor(\App\Models\BankAccountStatus::class)->constrained();
            $table->string('comments', 150)->nullable();
            $table->foreignIdFor(\App\Models\AccountType::class)->constrained();
            $table->foreignIdFor(\App\Models\Currency::class)->constrained();
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->softDeletes();
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
