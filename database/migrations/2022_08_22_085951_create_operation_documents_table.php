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
        Schema::create('operation_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Operation::class)->constrained();
            $table->enum('type', \App\Enums\DocumentType::asArray());
            $table->string("document_name", 150);
            $table->string("url", 300);
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
        Schema::dropIfExists('operation_documents');
    }
};
