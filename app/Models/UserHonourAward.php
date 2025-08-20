<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserHonourAward extends Model
{
    protected $fillable = ['user_id', 'award_title', 'year', 'description'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
