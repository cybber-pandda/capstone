<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodInventory extends Model
{
    use HasFactory;

    protected $table = 'food_inventories';

    protected $fillable = [
        'shelter_id',
        'name',
        'stock_in',
        'stock_out',
        'category',
    ];

    public function shelter()
    {
        return $this->belongsTo(Shelter::class);
    }

     // Accessor for remaining stock
     public function getRemainingStockAttribute()
     {
        return $this->stock_in - $this->stock_out;
     }
}
