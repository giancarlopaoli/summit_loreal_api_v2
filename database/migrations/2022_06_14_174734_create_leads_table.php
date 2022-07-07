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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->enum('contact_type', ['Natural', 'Juridica']);
            $table->foreignIdFor(\App\Models\DocumentType::class)->constrained();
            $table->foreignIdFor(\App\Models\Region::class)->constrained();
            $table->foreignIdFor(\App\Models\Sector::class)->constrained();
            $table->string('company_name', 150);
            $table->string('document_number', 12);
            $table->foreignIdFor(\App\Models\LeadContactType::class);
            $table->foreignIdFor(\App\Models\LeadStatus::class);
            $table->text('comments');
            $table->foreignIdFor(\App\Models\Executive::class);
            $table->enum('tracking_status', ['Pendiente', 'Completado', 'En curso', 'Seguimiento incumplido']);
            $table->timestamp('tracking_date');
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
        Schema::dropIfExists('leads');
    }
};
