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
        Schema::create('email_formats', function (Blueprint $table) {
            $table->id();
            $table->string('subject', 45);
            $table->text('body');
            $table->string('from_email', 255);
            $table->string('from_name', 255);
            $table->string('email_from_atscol', 45);
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
        Schema::dropIfExists('email_formats');
    }
};
