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
            $table->enum('status', ['pending', 'assigned', 'picked_up', 'on_the_way', 'shipped', 'delivered', 'cancelled'])->default('pending');
            $table->timestamp('delivery_date')->nullable();

            $table->decimal('rider_latitude', 10, 7)->nullable();
            $table->decimal('rider_longitude', 10, 7)->nullable();

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