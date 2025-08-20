<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employer_id',
        'title',
        'description',
        'applicant_deadline',
        'industry_id',
        'job_type',
        'required_skills',
        'salary_range',
        'career_level',
        'experience',
        'qualification',
        'company_name',
        'location',
        'file_attachment',
        'status',
        'view_count'
    ];

    protected $casts = [
        'required_skills' => 'array',
        'applicant_deadline' => 'date'
    ];

    public function employer()
    {
        return $this->belongsTo(User::class, 'employer_id');
    }
    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }

    public function favourites()
    {
        return $this->hasMany(FavouriteJob::class);
    }
    public function favouritedBy()
{
    return $this->belongsToMany(User::class, 'favourite_jobs', 'job_id', 'user_id')
        ->withTimestamps();
}


}
