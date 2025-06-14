<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventSettingQuestion extends Model
{
    use HasFactory;

    protected $table = 'event_setting_questions';

    protected $fillable = ['event_setting_id', 'question'];

    public function eventSetting()
    {
        return $this->belongsTo(EventSetting::class);
    }

    public function answers()
    {
        return $this->hasMany(EventVolunteerAnswer::class);
    }

}
