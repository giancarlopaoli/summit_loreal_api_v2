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
        Schema::connection('mysql2')->create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Area::class)->constrained();
            $table->string('code', 20);
            $table->string('description', 200);
            $table->integer('period');
            $table->decimal('initial_budget', 7,2);
            $table->decimal('final_budget', 7,2)->nullable();
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
        Schema::connection('mysql2')->dropIfExists('budgets');
    }
};
