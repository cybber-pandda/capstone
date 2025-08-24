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
            $table->unsignedBigInteger('prepared_by_id');
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
                'refunded',           // refund processed
                'reject_quotation'
            ])->default('pending');
            $table->integer('vat')->default(12);
            $table->date('b2b_delivery_date')->nullable();
            $table->decimal('delivery_fee', 10, 2)->nullable();
            $table->boolean('credit')->default(false);
            $table->decimal('credit_amount', 10, 2)->nullable();
            $table->string('credit_payment_type', 20)->nullable();
            $table->enum('payment_method', ['pay_now', 'pay_later'])->nullable();
            $table->boolean('cod_flg')->default(false);
            // $table->string('proof_payment')->nullable();
            // $table->string('reference_number', 30)->nullable();
            $table->text('pr_remarks')->nullable();
            $table->text('pr_remarks_cancel')->nullable();
            $table->text('date_issued')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('prepared_by_id')->references('id')->on('users')->onDelete('cascade');
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
