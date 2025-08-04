<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications'; 

    protected $fillable = [
        'user_id',
        'type',
        'message',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    // Optional: link to user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Optional: mark as read
    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    // Optional: check if unread
    public function isUnread()
    {
        return is_null($this->read_at);
    }
}
