<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->enum('status', [
                'pending',           // default, customer just submitted
                'quotation_sent',    // assistant sales officer has sent quotation
                'po_submitted',      // customer submitted purchase order
                'so_created',        // sales officer generated a sales order
                'delivery_in_progress', // delivery driver assigned
                'delivered',         // delivery completed
                'invoice_sent',      // sales invoice sent
                'cancelled',         // purchase request cancelled
                'returned',          // customer returned the item
                'refunded'           // refund processed
            ])->default('pending');
            $table->integer('vat')->nullable();
            $table->decimal('delivery_fee', 10, 2)->nullable();
            $table->boolean('credit')->default(false);
            $table->enum('payment_method', ['pay_now', 'pay_later'])->nullable();
            $table->string('proof_payment')->nullable();
            $table->string('reference_number', 30)->nullable();
            $table->text('pr_remarks')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('bank_id')->references('id')->on('banks')->onDelete('cascade');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_requests');
    }
}
