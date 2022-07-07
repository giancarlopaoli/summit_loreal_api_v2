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
        Schema::create('association_comissions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Association::class)->constrained();
            $table->decimal("comission_open", 7, 2);
            $table->decimal("comission_close", 7, 2);
            $table->boolean("active");
            $table->foreignId("updated_by")->constrained('users');
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
        Schema::dropIfExists('association_comissions');
    }
};
