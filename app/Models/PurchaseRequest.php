<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class PurchaseRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transaction_uuid','customer_id', 'prepared_by_id', 'status', 'vat', 
        'b2b_delivery_date', 'delivery_fee', 'credit', 'credit_amount', 
        'credit_payment_type', 'payment_method', 'cod_flg','pr_remarks', 
        'pr_remarks_cancel', 'date_issued'
    ];

    public function customer() { return $this->belongsTo(User::class, 'customer_id'); }
    public function address() { return $this->hasOne(B2BAddress::class, 'user_id', 'customer_id'); }
    public function detail() { return $this->hasOne(B2BDetail::class, 'user_id', 'customer_id'); }
    public function preparedBy() { return $this->belongsTo(User::class, 'prepared_by_id'); }
    public function items() { return $this->hasMany(PurchaseRequestItem::class); }
    public function returns() { return $this->hasMany(PurchaseRequestReturn::class); }
    public function refunds() { return $this->hasMany(PurchaseRequestRefund::class); }
    public function creditPayment() { return $this->hasOne(CreditPayment::class); }
    public function creditPartialPayments() { return $this->hasMany(CreditPartialPayment::class); }

    // <-- ADD THIS RELATIONSHIP
    public function paidPayments()
    {
        return $this->hasMany(PaidPayment::class, 'purchase_request_id');
    }

    public function createStraightCreditPayment($dueDate)
    {
        return $this->creditPayment()->create([
            'due_date' => $dueDate,
            'status' => 'pending'
        ]);
    }

    public function createPartialCreditPayments($startDate, $totalAmount)
    {
        $numPayments = 4;
        $paymentAmount = round($totalAmount / $numPayments, 2);

        for ($i = 1; $i <= $numPayments; $i++) {
            $dueDate = Carbon::parse($startDate)->addWeeks($i); 

            $this->creditPartialPayments()->create([
                'due_date' => $dueDate->toDateString(),
                'amount_to_pay' => $paymentAmount,
                'status' => 'pending'
            ]);
        }
    }
}
