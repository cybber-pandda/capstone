<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffSchedule extends Model
{
    use HasFactory;

    protected $table = 'staff_schedules';

    protected $fillable = [
        'staff_id', 'time_in', 'time_out', 'schedule_day'
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

}
