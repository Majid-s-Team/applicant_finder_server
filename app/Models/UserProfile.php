<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id','website','founded_date','sector','address',
        'gender','dob','public_private_profile','profile_url','job_title',
        'salary','industry_id','description'
    ];

    protected $casts = [
        'founded_date' => 'date',
        'dob' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function industry()
    {
        return $this->belongsTo(Industry::class);
    }
}