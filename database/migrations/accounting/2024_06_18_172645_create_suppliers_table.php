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
        Schema::connection('mysql2')->create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->foreignIdFor(\App\Models\DocumentType::class);
            $table->string('document_number', 15);
            $table->string('email', 100)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('detraction_account', 30)->nullable();
            $table->string('logo_url', 200)->nullable();
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
        Schema::connection('mysql2')->dropIfExists('suppliers');
    }
};
