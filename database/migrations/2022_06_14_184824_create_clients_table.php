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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('last_name', 255);
            $table->string('mothers_name', 255);
            $table->foreignIdFor(\App\Models\DocumentType::class)->constrained();
            $table->string('document_number', 12);
            $table->string('phone', 20);
            $table->string('email', 100);
            $table->string('address', 255);
            $table->date('birthdate');
            $table->foreignIdFor(\App\Models\District::class)->constrained();
            $table->foreignIdFor(\App\Models\Country::class)->constrained();
            $table->foreignIdFor(\App\Models\EconomicActivity::class)->constrained();
            $table->foreignIdFor(\App\Models\Profession::class)->constrained();
            $table->enum('customer_type', ['PN', 'PJ']);
            $table->enum('type', ['Cliente', 'PL']);
            $table->foreignIdFor(\App\Models\ClientStatus::class);
            $table->string('accountable_email', 255)->nullable();
            $table->string('comments', 200)->nullable();
            $table->string('funds_source', 255)->nullable();
            $table->string('funds_comments', 255)->nullable();
            $table->string('other_funds_comments', 255)->nullable();
            $table->boolean('pep')->default(false);
            $table->string('pep_company', 100)->nullable();
            $table->string('pep_position', 100)->nullable();
            $table->integer('corfid_id')->nullable();
            $table->string('corfid_message', 255)->nullable();
            $table->foreignIdFor(\App\Models\Association::class)->nullable()->constrained();
            $table->timestamp('billex_approved_at')->nullable();
            $table->timestamp('corfid_approved_at')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->foreignIdFor(\App\Models\Executive::class)->nullable()->constrained();
            $table->foreignIdFor(\App\Models\TrackingPhase::class)->nullable()->constrained();
            $table->timestamp('tracking_date')->nullable();
            $table->date('comission_start_date')->nullable();
            $table->decimal('comission', 5, 4)->nullable();
            $table->unsignedInteger("invoice_to")->nullable();
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
        Schema::dropIfExists('clients');
    }
};
