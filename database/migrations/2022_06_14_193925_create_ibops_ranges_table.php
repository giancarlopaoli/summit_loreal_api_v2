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
        Schema::create('ibops_ranges', function (Blueprint $table) {
            $table->id();
            $table->decimal('min_range', 10, 2);
            $table->decimal('max_range', 10, 2);
            $table->foreignIdFor(\App\Models\Currency::class)->constrained();
            $table->decimal('comission_spread', 6, 2);
            $table->decimal('spread', 6, 2);
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
        Schema::dropIfExists('ibops_ranges');
    }
};
