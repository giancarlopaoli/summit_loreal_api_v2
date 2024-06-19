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
        Schema::connection('mysql2')->create('sale_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Sale::class)->constrained();
            $table->string('description', 200);
            $table->integer('quantity');
            $table->decimal('unit_amount', 7,2);
            $table->decimal('igv', 7,2);
            $table->decimal('discount', 7,2);
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
        Schema::connection('mysql2')->dropIfExists('sale_lines');
    }
};
