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
        Schema::table('connectivities', function (Blueprint $table) {
            $table->string('name', 100)->nullable();
            $table->foreignIdFor(\App\Models\ConnectivityCategory::class)->nullable()->constrained()->after('password');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('connectivities', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropForeign('connectivities_connectivity_category_id_foreign');
            $table->dropColumn('connectivity_category_id');
        });
    }
};
