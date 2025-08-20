<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLanguage extends Model
{
    protected $fillable = ['user_id', 'language', 'proficiency']; 
    // proficiency = basic, intermediate, fluent, native

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

