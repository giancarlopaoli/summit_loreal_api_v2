<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->enum('type', ['Participante', 'Expositor','Administrador']);
            $table->string('phone', 30)->nullable();
            $table->string('country', 30)->nullable();
            $table->string('city', 50)->nullable();
            $table->enum('document_type', ['DNI', 'Pasaporte']);
            $table->string('document_number', 20);
            $table->string('preferences', 50)->nullable();
            $table->string('password');
            $table->boolean('accepts_publicity')->nullable()->default(false);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
