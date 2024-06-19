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
        Schema::connection('mysql2')->create('payroll_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Payroll::class)->constrained();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('contract_url', 300)->nullable();
            $table->foreignId('created_by')->nullable();
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
        Schema::connection('mysql2')->dropIfExists('payroll_contracts');
    }
};
