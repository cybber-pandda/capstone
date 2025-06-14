<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QRCode extends Model
{
    use HasFactory;

    protected $table = 'qrcodes';

    protected $fillable = [
        'animal_id', 'qr_code_image_url',
    ];

    public function animal()
    {
        return $this->belongsTo(Pet::class);
    }
}
