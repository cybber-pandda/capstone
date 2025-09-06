<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('delivery_rider_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->unsignedInteger('quantity')->default(0);
            $table->string('tracking_number')->nullable();
            $table->enum('status', ['pending', 'assigned', 'on_the_way', 'delivered', 'cancelled',  'returned', 'refunded'])->default('pending');
            $table->timestamp('delivery_date')->nullable();
            $table->string('proof_delivery')->nullable();
            $table->text('delivery_remarks')->nullable();
            $table->integer('sales_invoice_flg')->default(0);
            
            $table->decimal('delivery_latitude', 10, 7)->default('13.9655000'); // default store lat
            $table->decimal('delivery_longitude', 10, 7)->default('121.5348000'); // default store lng

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
        Schema::dropIfExists('deliveries');
    }
}