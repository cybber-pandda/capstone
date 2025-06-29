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
        'expiry_date'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
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
}
