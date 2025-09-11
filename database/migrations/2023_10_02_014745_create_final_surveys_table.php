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
        Schema::create('final_surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\User::class)->nullable()->constrained();
            $table->integer('question_1');
            $table->integer('question_2');
            $table->integer('question_3');
            $table->integer('question_4');
            $table->integer('question_5');
            $table->integer('question_6');
            $table->integer('question_7');
            $table->integer('question_8');
            $table->string('question_9', 500);
            $table->string('question_10', 500);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('final_surveys');
    }
};
