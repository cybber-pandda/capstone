<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrReserveStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pr_reserve_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pr_id')->index()->comment('Purchase Request ID');
            $table->unsignedBigInteger('product_id')->index()->comment('Product ID');
            $table->decimal('qty', 15, 3)->default(0)->comment('Reserved Quantity');
            $table->enum('status', ['pending', 'approved', 'cancelled', 'returned', 'completed'])
                ->default('pending')
                ->comment('Reserve stock status');
            $table->timestamps();

            // Foreign keys (optional if you have existing tables)
            $table->foreign('pr_id')->references('id')->on('purchase_requests')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pr_reserve_stocks');
    }
}
