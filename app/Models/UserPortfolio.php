<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPortfolio extends Model
{
    protected $fillable = ['user_id', 'title', 'video_url', 'image_url'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
