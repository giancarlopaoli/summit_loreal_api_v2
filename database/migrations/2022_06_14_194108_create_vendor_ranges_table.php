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
        Schema::create('vendor_ranges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('clients');
            $table->decimal('min_range', 10, 2);
            $table->decimal('max_range', 10, 2);
            $table->boolean('active');
            $table->foreignId('updated_by')->constrained('users');
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
        Schema::dropIfExists('vendor_ranges');
    }
};
