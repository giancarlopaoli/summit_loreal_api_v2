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
        Schema::create('lead_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Lead::class)->constrained();
            $table->string('names', 100);
            $table->string('last_names', 100);
            $table->string('area', 30);
            $table->string('job_title', 30);
            $table->boolean('main_contact');
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
        Schema::dropIfExists('lead_contacts');
    }
};
