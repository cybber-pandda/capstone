<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventVolunteer extends Model
{
    use HasFactory;

    protected $table = 'event_volunteers';

    protected $fillable = [
        'user_id',
        'event_setting_id',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tasks()
    {
        return $this->hasMany(EventVolunteerTask::class);
    }
}
