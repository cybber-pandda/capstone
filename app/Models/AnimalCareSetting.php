<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnimalCareSetting extends Model
{
    use HasFactory;

    protected $table = 'animalcare_settings';

    protected $fillable = [
        'tutorial_question','photo','tutorial_description'
    ];
}
