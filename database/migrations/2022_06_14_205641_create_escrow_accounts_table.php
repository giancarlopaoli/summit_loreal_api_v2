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
        Schema::create('escrow_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Bank::class)->constrained();
            $table->string('account_number', 30);
            $table->string('cci_number', 30);
            $table->foreignIdFor(\App\Models\Currency::class)->constrained();
            $table->string('beneficiary_name', 100);
            $table->string('beneficiary_address', 255);
            $table->foreignIdFor(\App\Models\DocumentType::class)->constrained();
            $table->string('document_number', 12);
            $table->boolean('active');
            $table->integer('corfid_id');
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
        Schema::dropIfExists('escrow_accounts');
    }
};
