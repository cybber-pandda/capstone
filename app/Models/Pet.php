<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pet extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'animals';

    protected $fillable = [
        'photo',
        'video_url',
        'name',
        'species',
        'breed',
        'age',
        'gender',
        'color',
        'size',
        'characteristics',
        'medical_history',
        'pet_story',
        'current_status',
        'shelter_id',
        'isRescuedNote',
        'isSurrenderedNote',
        'registration_date',
    ];

    public function healthRecords()
    {
        return $this->hasMany(HealthRecord::class, 'animal_id');
    }

    public function qrCode()
    {
        return $this->hasOne(QRCode::class, 'animal_id');
    }

    public function shelter()
    {
        return $this->belongsTo(Shelter::class);
    }

    public function shelter_requirement()
    {
        return $this->belongsTo(AdoptionRequirement::class, 'shelter_id', 'shelter_id');
    }
}
