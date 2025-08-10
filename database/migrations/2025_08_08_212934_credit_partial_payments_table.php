<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreditPartialPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::create('credit_partial_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_request_id');
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->decimal('paid_amount', 10, 2);
            $table->date('due_date');
            $table->decimal('amount_to_pay', 10, 2);
            $table->date('paid_date')->nullable();
            $table->enum('status', ['pending','unpaid','paid','overdue'])->default('pending');
            $table->string('proof_payment')->nullable();
            $table->string('reference_number')->nullable();
            $table->date('approved_at')->nullable();
            $table->integer('approved_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('purchase_request_id')->references('id')->on('purchase_requests')->onDelete('cascade');
            $table->foreign('bank_id')->references('id')->on('banks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('credit_partial_payments');
    }
}
