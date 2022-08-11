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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('last_name', 50);
            $table->string('email', 100)->unique();
            $table->foreignIdFor(\App\Models\DocumentType::class)->constrained();
            $table->string('document_number', 15)->unique();
            $table->string('phone', 30);
            $table->integer('tries')->default(0);
            $table->string('password');
            $table->timestamp('last_login')->nullable();
            $table->enum('status', \App\Enums\UserStatus::asArray());
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
        Schema::dropIfExists('users');
    }
};
