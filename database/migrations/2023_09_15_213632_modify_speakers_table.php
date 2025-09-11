<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('speakers', function (Blueprint $table) {
            $table->string('document')->nullable()->after('image');
            $table->string('document2')->nullable()->after('document');
            $table->string('document3')->nullable()->after('document2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('speakers', function (Blueprint $table) {
            $table->dropColumn('document');
            $table->dropColumn('document2');
            $table->dropColumn('document3');
        });
    }
};
