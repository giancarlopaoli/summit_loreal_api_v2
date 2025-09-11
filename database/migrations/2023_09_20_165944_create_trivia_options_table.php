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
        Schema::create('trivia_options', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\TriviaQuestion::class)->constrained();
            $table->string('question_es', 350);
            $table->string('question_en', 350);
            $table->boolean('correct');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trivia_options');
    }
};
