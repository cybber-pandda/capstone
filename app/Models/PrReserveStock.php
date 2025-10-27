<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PrReserveStock extends Model
{
    use HasFactory;

    protected $table = 'pr_reserve_stocks';

    protected $fillable = [
        'pr_id',
        'product_id',
        'qty',
        'status',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class, 'pr_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Business Logic
    |--------------------------------------------------------------------------
    */

    /**
     * Reserve stock for a purchase request.
     * Moves requested quantity from available stock to reserved.
     */
    /*
    public static function reserveForPurchaseRequest(PurchaseRequest $purchaseRequest)
    {
        foreach ($purchaseRequest->items as $item) {
            $product = $item->product;

            if (!$product) {
                throw new \Exception('Product not found for PR item #' . $item->id);
            }

            // Use stock batches for accurate stock
            $availableStock = $product->stockBatches()->sum('remaining_quantity') - self::getTotalReservedStock($product->id);

            if ($availableStock < $item->quantity) {
                throw new \Exception('Insufficient stock for product: ' . $product->name);
            }


            DB::transaction(function () use ($purchaseRequest, $product, $item) {


                self::create([
                    'pr_id' => $purchaseRequest->id,
                    'product_id' => $product->id,
                    'qty' => $item->quantity,
                    'status' => 'pending',
                ]);
            });
        }
    } */
       public static function reserveForPurchaseRequest(PurchaseRequest $purchaseRequest)
        {
            foreach ($purchaseRequest->items as $item) {
                $product = $item->product;

                if (!$product) {
                    throw new \Exception('Product not found for PR item #' . $item->id);
                }

                // Use stock batches for accurate stock
                $availableStock = $product->stockBatches()->sum('remaining_quantity');

                if ($availableStock < $item->quantity) {
                    throw new \Exception('Insufficient stock for product: ' . $product->name);
                }


                DB::transaction(function () use ($purchaseRequest, $product, $item) {


                    // 1️⃣ Create reserve record as pending
                    $reserve = self::create([
                        'pr_id' => $purchaseRequest->id,
                        'product_id' => $product->id,
                        'qty' => $item->quantity,
                        'status' => 'pending', // still pending
                    ]);

                    // 2️⃣ Deduct stock immediately (even if pending)
                    StockBatch::reduceFIFO($product->id, $item->quantity, 'Reserved (Pending PR#' . $purchaseRequest->id . ')');

                    // 3️⃣ Log inventory as out (reserved)
                    Inventory::create([
                        'product_id' => $product->id,
                        'type' => 'out',
                        'quantity' => $item->quantity,
                        'reason' => 'reserved (pending)',
                    ]);
                });
            }
        }


    /**
     * Mark reserved stock as approved (assistant sales approved PR).
     * Stock is reduced from batches if not already reduced.
     */
    /*
    public static function approveReservation($prId)
    {
        $reserves = self::where('pr_id', $prId)
            ->where('status', 'pending')
            ->get();

        foreach ($reserves as $reserve) {
            $product = $reserve->product;

            if (!$product) {
                throw new \Exception('Product not found for reserved item #' . $reserve->id);
            }

            DB::transaction(function () use ($reserve, $product) {
                // Reduce stock from batches (FIFO)
                StockBatch::reduceFIFO($product->id, $reserve->qty, 'Approve PR#' . $reserve->pr_id);

                // Log inventory as "out"
                Inventory::create([
                    'product_id' => $product->id,
                    'type' => 'out',
                    'quantity' => $reserve->qty,
                    'reason' => 'sold',
                ]);

                // Update reserve status to approved
                $reserve->update(['status' => 'approved']);
            });
        }
    }
    */
        public static function approveReservation($prId)
    {
        $reserves = self::where('pr_id', $prId)
            ->where('status', 'pending')
            ->get();

        foreach ($reserves as $reserve) {
            $reserve->update(['status' => 'approved']);
        }
    }

    /**
     * Mark reserved stock as completed (delivery successful).
     * Stock was already reduced when reserved, so just update status.
     */
    public static function completeReservation($prId)
    {
        $reserve = self::where('pr_id', $prId)->first();

        if ($reserve) {
            $reserve->update(['status' => 'completed']);
        }
    }

    /**
     * Release reserved stock (PR cancelled or rejected).
     * Returns stock to inventory.
     */
    public static function releaseReservedStock($prId, $type = 'returned')
    {
        $reserve = self::where('pr_id', $prId)->first();

        if ($reserve && in_array($reserve->status, ['pending', 'approved'])) {
            DB::transaction(function () use ($reserve, $type) {
                // Restore stock back to batches
                StockBatch::restoreFIFO($reserve->product_id, $reserve->qty, 'Released from PR#' . $reserve->pr_id);

                // Log inventory as "in"
                Inventory::create([
                    'product_id' => $reserve->product_id,
                    'type' => 'in',
                    'quantity' => $reserve->qty,
                    'reason' => 'returned',
                ]);

                // Update reserve status
                $reserve->update(['status' => $type]);
            });
        }
    }


    /**
     * Get total reserved quantity for a specific product.
     */
    public static function getTotalReservedStock($productId)
    {
        return self::where('product_id', $productId)
            ->whereIn('status', ['pending', 'approved'])
            ->sum('qty');
    }
}
