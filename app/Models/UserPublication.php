<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPublication extends Model
{
    protected $fillable = ['user_id', 'title', 'publisher', 'publication_date', 'url', 'description'];

    protected $casts = [
        'publication_date' => 'date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

