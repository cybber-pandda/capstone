<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchingCriteria extends Model
{
    protected $table = 'matching_criteria';

    // MatchingCriteria belongs to an Animal
    public function animal()
    {
        return $this->belongsTo(Pet::class, 'animal_id');
    }

    // MatchingCriteria belongs to a User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id'); // Assuming `user_id` is the foreign key in matching_criteria
    }
}
