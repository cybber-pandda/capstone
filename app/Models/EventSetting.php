<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventSetting extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'event_settings';

    protected $fillable = [
        'user_id',
        'shelter_id',
        'event_title',
        'description',
        'start_date',
        'end_date',
        'event_time',
        'location',
        'capacity',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shelter()
    {
        return $this->belongsTo(Shelter::class);
    }

    public function questions()
    {
        return $this->hasMany(EventSettingQuestion::class);
    }

    public function eventVolunteers()
    {
        return $this->hasMany(EventVolunteer::class);
    }

}
