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
        Schema::create('representatives', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Client::class)->constrained();
            $table->enum('representative_type', ['Socio', 'Representante Legal']);
            $table->foreignIdFor(\App\Models\DocumentType::class)->constrained();
            $table->string('document_number', 100);
            $table->string('names', 150);
            $table->string('last_name', 50)->nullable();
            $table->string('mothers_name', 50)->nullable();
            $table->boolean('pep');
            $table->string('pep_company', 100)->nullable();
            $table->string('pep_position', 100)->nullable();
            $table->string('share', 20)->nullable();
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
        Schema::dropIfExists('representatives');
    }
};
