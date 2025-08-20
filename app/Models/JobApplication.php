<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    protected $fillable = [
        'job_id',
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'current_job_title',
        'current_job_salary',
        'message',
        'resume_link',
        'apply_type',
        'status'
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
