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
        Schema::create('lead_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Lead::class)->constrained();
            $table->foreignIdFor(\App\Models\TrackingStatus::class)->constrained();
            $table->foreignIdFor(\App\Models\TrackingForm::class)->constrained();
            $table->foreignIdFor(\App\Models\TrackingPhase::class)->constrained();
            $table->foreignIdFor(\App\Models\LeadContact::class)->constrained();
            $table->string('comments', 45);
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
        Schema::dropIfExists('lead_trackings');
    }
};
