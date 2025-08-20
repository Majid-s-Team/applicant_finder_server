<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserExpertise extends Model
{
    protected $fillable = ['user_id', 'label', 'percentage'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
