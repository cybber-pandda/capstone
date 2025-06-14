<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventVolunteerTask extends Model
{
    use HasFactory;

    protected $table = 'event_volunteer_tasks';

    protected $fillable = [
        'user_id',
        'event_volunteer_id',
        'task',
        'task_percentage',
        'task_status',
    ];
}
