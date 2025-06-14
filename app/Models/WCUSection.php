<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WCUSection extends Model
{
    use HasFactory;

    protected $table = 'wcu_section';

    protected $fillable = [
        'icon', 'title', 'content'
    ];
}
