<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserExperience extends Model
{
    protected $fillable = ['user_id', 'title', 'from_date', 'to_date', 'company', 'description', 'is_present'];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'is_present' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
