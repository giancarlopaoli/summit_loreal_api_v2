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
        Schema::create('client_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Client::class)->constrained();
            $table->enum('tracking_status', ['Bienvenida','Operará','No interesado','No contesta','Datos incorrectos','Seguimiento incumplido','Reasignado'])->nullable();
            $table->foreignIdFor(\App\Models\TrackingForm::class)->constrained();
            $table->text('comments')->nullable();
            $table->foreignId('created_by')->constrained('users');
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
        Schema::dropIfExists('client_trackings');
    }
};
