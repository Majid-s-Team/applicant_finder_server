<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Academic extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'degree_name', 'institution', 'start_date', 'end_date', 'grade'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
