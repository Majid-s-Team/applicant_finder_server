<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRecommendation extends Model
{
    protected $fillable = ['user_id', 'recommended_by', 'content'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function recommender()
    {
        return $this->belongsTo(User::class, 'recommended_by');
    }
}
