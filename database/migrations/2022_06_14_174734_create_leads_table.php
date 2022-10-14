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
            $table->foreignIdFor(\App\Models\Region::class)->nullable()->constrained();
            $table->foreignIdFor(\App\Models\Sector::class)->nullable()->constrained();
            $table->string('company_name', 150);
            $table->string('document_number', 12);
            $table->foreignIdFor(\App\Models\LeadContactType::class)->nullable()->constrained();
            $table->foreignIdFor(\App\Models\LeadStatus::class)->constrained();
            $table->integer('client_id')->nullable();
            $table->text('comments')->nullable();
            $table->foreignIdFor(\App\Models\Executive::class)->nullable()->constrained();
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
