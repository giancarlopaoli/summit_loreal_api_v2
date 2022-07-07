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
        Schema::create('vendor_spreads', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\VendorRange::class);
            $table->decimal('buying_spread', 7, 2);
            $table->decimal('selling_spread', 7, 2);
            $table->boolean('active');
            $table->foreignIdFor(\App\Models\User::class);
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
        Schema::dropIfExists('vendor_spreads');
    }
};
