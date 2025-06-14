<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $table = 'expenses';

    protected $fillable = [
        'shelter_id',
        'name',
        'qty',
        'price',
        'proof_receipt'
    ];

    public function shelter()
    {
        return $this->belongsTo(Shelter::class);
    }
}
