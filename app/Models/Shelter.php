<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Shelter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'owner_name',
        'owner_phone',
        'shelter_name',
        'shelter_address',
        'shelter_limit_population'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function animals()
    {
        return $this->hasMany(Pet::class, 'shelter_id')->withTrashed();
    }

    public function adoptionApplications()
    {
        return $this->hasManyThrough(
            AdoptionApplication::class,
            Pet::class,
            'shelter_id', // Foreign key on the Pet table...
            'animal_id', // Foreign key on the AdoptionApplication table...
            'id', // Local key on the Shelter table...
            'id' // Local key on the Pet table...
        );
    }

    
}
