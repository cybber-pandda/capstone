<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManualEmailOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('manual_email_order', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name')->nullable();
            $table->string('customer_type', 20)->nullable();
            $table->string('customer_email');
            $table->string('customer_address')->nullable();
            $table->string('customer_phone_number')->nullable();
            $table->string('order_date')->nullable();
            $table->json('purchase_request')->nullable();
            $table->string('remarks')->nullable();
            $table->integer('delivery_fee')->default(0);
            $table->enum('status', ['pending', 'waiting','approve', 'rejected'])->default('pending');
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
        Schema::dropIfExists('manual_email_order');
    }
}
