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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Campaign::class)->constrained();
            $table->string('code', 45);
            $table->string('description', 45);
            $table->enum('type', \App\Enums\CouponType::asArray());
            $table->enum('class', ['Normal', 'Primera Operacion']);
            $table->decimal('value', 7, 2);
            $table->boolean('active');
            $table->integer('limit_total');
            $table->integer('limit_individual');
            $table->enum('assigned_to', ['PN', 'PJ','Todos']);
            $table->date('start_date');
            $table->date('end_date');
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
        Schema::dropIfExists('coupons');
    }
};
