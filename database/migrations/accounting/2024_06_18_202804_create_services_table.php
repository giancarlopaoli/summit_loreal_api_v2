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
        Schema::connection('mysql2')->create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Budget::class)->constrained();
            $table->foreignIdFor(\App\Models\Supplier::class)->constrained();
            $table->string('name', 100);
            $table->string('description', 200)->nullable();
            $table->decimal('amount', 7,2);
            $table->foreignIdFor(\App\Models\Currency::class);
            $table->decimal('exchange_rate', 5, 4)->nullable();
            $table->enum('frequency', ['Compra única', 'Mensual','Anual','Otro']);
            $table->foreignId('updated_by')->nullable();
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
        Schema::connection('mysql2')->dropIfExists('services');
    }
};
