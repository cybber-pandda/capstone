<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdoptionApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'adopter_id',
        'animal_id',
        'requirement_file',
        'application_date',
        'status',
        'notes',
    ];

    public function animal()
    {
        return $this->belongsTo(Pet::class, 'animal_id');
    }

    public function adopter()
    {
        return $this->belongsTo(Adopter::class, 'adopter_id');
    }
}
