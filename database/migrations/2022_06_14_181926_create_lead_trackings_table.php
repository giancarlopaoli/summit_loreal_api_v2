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
            $table->foreignIdFor(\App\Models\TrackingForm::class)->nullable()->constrained();
            $table->foreignIdFor(\App\Models\TrackingPhase::class)->constrained();
            $table->foreignIdFor(\App\Models\LeadContact::class)->nullable()->constrained();
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
        Schema::dropIfExists('lead_trackings');
    }
};
