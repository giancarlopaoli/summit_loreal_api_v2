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
        Schema::create('agenda_speakers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Agenda::class)->constrained();
            $table->string('name', 100);
            $table->string('specialty_pen', 200);
            $table->string('specialty_usd', 200);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agenda_speakers');
    }
};
