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
            $table->string('document_number', 15);
            $table->string('names', 150);
            $table->string('last_name', 50);
            $table->string('mothers_name', 50);
            $table->boolean('pep');
            $table->string('pep_company', 100);
            $table->string('pep_position', 100);
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
