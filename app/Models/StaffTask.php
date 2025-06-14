<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffTask extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'staff_tasks';

    protected $fillable = [
        'staff_id', 'task', 'task_percentage', 'task_status'
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

}
