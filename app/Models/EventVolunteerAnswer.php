<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventVolunteerAnswer extends Model
{
    use HasFactory;

    protected $table = 'event_volunteer_answers';

    protected $fillable = [
        'user_id',
        'event_volunteer_id',
        'event_setting_question_id',
        'answer',
    ];
    
}
