<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'category_id',
        'name',
        'sku',
        'description',
        'price',
        'discount',
        'discounted_price',
        // 'expiry_date',
        'maximum_stock',
        'critical_stock_level'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id')->withTrashed();
    }

    public function productImages()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }

    public function ratings()
    {
        return $this->hasMany(ProductRating::class);
    }

    public function getStockInAttribute()
    {
        return $this->inventories()->where('type', 'in')->sum('quantity');
    }

    public function getStockOutAttribute()
    {
        return $this->inventories()->where('type', 'out')->sum('quantity');
    }

    public function getCurrentStockAttribute()
    {
        return $this->stock_in - $this->stock_out;
    }

    public function stockBatches()
    {
        return $this->hasMany(StockBatch::class, 'product_id');
    }

    public function prReserveStocks()
    {
        return $this->hasMany(PrReserveStock::class, 'product_id');
    }
    public function purchaseRequestItems()
    {
    return $this->hasMany(PurchaseRequestItem::class, 'product_id');
    }
}
