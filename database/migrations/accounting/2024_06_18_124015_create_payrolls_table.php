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
        Schema::connection('mysql2')->create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('last_name', 50);
            $table->string('mothers_name', 50);
            $table->foreignIdFor(\App\Models\DocumentType::class);
            $table->string('document_number', 15);
            $table->string('email', 100)->unique();
            $table->string('phone', 30)->nullable();
            $table->enum('status', ['Activo', 'Inactivo']);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('birthdate');
            $table->enum('gender', ['Masculino', 'Femenino']);
            $table->enum('marital_status', ['Soltero', 'Casado', 'Viudo', 'Divorciado']);
            $table->enum('type', ['Planilla', 'Independiente']);
            $table->foreignIdFor(\App\Models\Budget::class)->constrained();
            $table->foreignIdFor(\App\Models\Afp::class)->constrained();
            $table->string('afp_cussp', 30)->nullable();
            $table->string('account_number', 30)->nullable();
            $table->string('cci_number', 35)->nullable();
            $table->foreignIdFor(\App\Models\Bank::class)->nullable();
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
        Schema::connection('mysql2')->dropIfExists('payrolls');
    }
};
