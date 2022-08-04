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
        Schema::create('ranges', function (Blueprint $table) {
            $table->id();
            $table->decimal('min_range', 10, 2);
            $table->decimal('max_range', 10, 2);
            $table->decimal('comission_open', 7, 2);
            $table->decimal('comission_close', 7, 2);
            $table->decimal('spread_open', 7, 2);
            $table->decimal('spread_close', 7, 2);
            $table->boolean('active')->default(true);
            $table->foreignId('modified_by')->nullable()->constrained('users');
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
        Schema::dropIfExists('ranges');
    }
};
