<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

use App\Models\StockMovement;


class StockBatch extends Model
{
    use HasFactory;

    protected $table = 'stock_batches';

    protected $fillable = [
        'product_id',
        'inventory_id',
        'quantity',
        'remaining_quantity',
        'cost_price',
        'received_date',
        'expiry_date',
        'note'
    ];

    protected $casts = [
        'received_date' => 'datetime',
        'cost_price' => 'decimal:2',
    ];

    /**
     * Relationships
     */

    // Each batch belongs to one product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Accessors / Helpers
     */

    // Calculate how much of the batch was already used
    public function getUsedQuantityAttribute()
    {
        return $this->quantity - $this->remaining_quantity;
    }

    // Determine if the batch is fully consumed
    public function getIsDepletedAttribute()
    {
        return $this->remaining_quantity <= 0;
    }

    /**
     * Apply FIFO stock reduction logic.
     *
     * @param int $productId
     * @param int $quantity
     * @return void
     */
    // public static function reduceFIFO($productId, $quantity, $note = null)
    // {
    //     $neededQty = $quantity;

    //     $batches = self::where('product_id', $productId)
    //         ->where('remaining_quantity', '>', 0)
    //         ->orderBy('received_date', 'asc')
    //         ->get();

    //     foreach ($batches as $batch) {
    //         if ($neededQty <= 0) break;

    //         $takeQty = min($batch->remaining_quantity, $neededQty);
    //         $batch->decrement('remaining_quantity', $takeQty);

    //         // Create a StockMovement record
    //         StockMovement::create([
    //             'product_id' => $productId,
    //             'batch_id' => $batch->id,
    //             'type' => 'out',
    //             'quantity' => $takeQty,
    //             'reference' => $note,
    //         ]);

    //         if ($note) {
    //             $batch->note = trim(($batch->note ? $batch->note . ' | ' : '') . $note);
    //             $batch->save();
    //         }

    //         $neededQty -= $takeQty;
    //     }
    // }

    public static function reduceFIFO($productId, $quantity, $note = null)
    {
        $neededQty = $quantity;

        $today = now()->toDateString();

        $batches = self::where('product_id', $productId)
            ->where('remaining_quantity', '>', 0)
            ->where(function ($q) use ($today) {
                $q->whereNull('expiry_date')  // batches with no expiration
                    ->orWhere('expiry_date', '>=', $today); // not expired
            })
            ->orderBy('received_date', 'asc')
            ->get();

        foreach ($batches as $batch) {
            if ($neededQty <= 0) break;

            $takeQty = min($batch->remaining_quantity, $neededQty);
            $batch->decrement('remaining_quantity', $takeQty);

            // Create a StockMovement record
            StockMovement::create([
                'product_id' => $productId,
                'batch_id' => $batch->id,
                'type' => 'out',
                'quantity' => $takeQty,
                'reference' => $note,
            ]);

            if ($note) {
                $batch->note = trim(($batch->note ? $batch->note . ' | ' : '') . $note);
                $batch->save();
            }

            $neededQty -= $takeQty;
        }

        if ($neededQty > 0) {
            // Optional: throw exception or log if stock not enough
            throw new \Exception("Insufficient non-expired stock for product ID {$productId}. Remaining needed: {$neededQty}");
        }
    }

    /**
     * Restore stock (used for returns, cancellations, etc.)
     * Adds quantity back to batches based on FIFO (First-In, First-Out)
     */
    public static function restoreFIFO($productId, $quantity, $note = null)
    {
        $restoreQty = $quantity;

        // Fetch oldest batches first (FIFO)
        $batches = self::where('product_id', $productId)
            ->orderBy('received_date', 'asc')
            ->get();

        foreach ($batches as $batch) {
            if ($restoreQty <= 0) break;

            // Maximum we can restore into this batch
            $availableSpace = $batch->quantity - $batch->remaining_quantity;
            $addQty = min($availableSpace, $restoreQty);

            $batch->increment('remaining_quantity', $addQty);

            // Log the movement
            if ($addQty > 0) {
                StockMovement::create([
                    'product_id' => $productId,
                    'batch_id' => $batch->id,
                    'type' => 'in',
                    'quantity' => $addQty,
                    'reference' => $note,
                ]);
            }

            $restoreQty -= $addQty;
        }

        // Optional: if leftover quantity, create a new batch
        if ($restoreQty > 0) {
            $newBatch = self::create([
                'product_id' => $productId,
                'quantity' => $restoreQty,
                'remaining_quantity' => $restoreQty,
                'received_date' => now(),
                'note' => $note ?? 'Manual stock return',
            ]);

            StockMovement::create([
                'product_id' => $productId,
                'batch_id' => $newBatch->id,
                'type' => 'in',
                'quantity' => $restoreQty,
                'reference' => $note ?? 'Manual stock return',
            ]);
        }
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }
}
