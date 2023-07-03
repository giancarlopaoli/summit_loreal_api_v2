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
        Schema::create('special_exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Client::class)->constrained();
            $table->foreignId('vendor_id')->constrained('clients');
            $table->decimal('buying', 6, 4)->nullable();
            $table->decimal('selling', 6, 4)->nullable();
            $table->integer('duration_time');
            $table->boolean('active');
            $table->timestamp('finished_at');
            $table->foreignId('updated_by')->nullable()->constrained('users');
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
        Schema::dropIfExists('special_exchange_rates');
    }
};
