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
        Schema::create('operations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20);
            $table->enum('class', ['Inmediata', 'Programada', 'Interbancaria']);
            $table->enum('type', ['Compra', 'Venta']);
            $table->foreignIdFor(\App\Models\Client::class)->constrained();
            $table->foreignIdFor(\App\Models\User::class)->constrained();
            $table->decimal('amount', 11, 2);
            $table->foreignIdFor(\App\Models\Currency::class)->constrained();
            $table->decimal('exchange_rate', 7,6 );
            $table->decimal('comission_spread', 7,2 );
            $table->decimal('comission_amount', 8,2 );
            $table->decimal('igv', 6,2 );
            $table->decimal('spread', 7,2 );
            $table->foreignIdFor(\App\Models\OperationStatus::class)->constrained();
            $table->string('transfer_number', 45);
            $table->integer('corfid_id');
            $table->string('corfid_mesage', 255);
            $table->string('invoice_number', 45);
            $table->string('invoice_url', 45);
            $table->string('backup_status', 45);
            $table->decimal('base_exchange_rate', 7, 6);
            $table->foreignIdFor(\App\Models\Coupon::class)->constrained();
            $table->string('coupon_code', 45);
            $table->string('coupon_type', 30);
            $table->decimal('coupon_value', 5, 2);
            $table->timestamp('operation_date');
            $table->timestamp('funds_confirmation_table')->nullable();
            $table->timestamp('deposit_date')->nullable();
            $table->timestamp('sign_date')->nullable();
            $table->timestamp('mail_instructions')->nullable();
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
        Schema::dropIfExists('operations');
    }
};
