<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'password',
        'accept_terms',
        'otp',
        'otp_expires_at',
        'role'
    ];

    protected $hidden = ['password', 'remember_token', 'otp'];

    protected $casts = [
        'otp_expires_at' => 'datetime',
    ];

    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function academics()
    {
        return $this->hasMany(Academic::class);
    }
    public function skills()
    {
        return $this->hasMany(UserSkill::class);
    }

    public function educations()
    {
        return $this->hasMany(UserEducation::class);
    }

    public function experiences()
    {
        return $this->hasMany(UserExperience::class);
    }

    public function portfolios()
    {
        return $this->hasMany(UserPortfolio::class);
    }

    public function expertises()
    {
        return $this->hasMany(UserExpertise::class);
    }

    public function honoursAwards()
    {
        return $this->hasMany(UserHonourAward::class);
    }
    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }

    public function favourites()
    {
        return $this->hasMany(FavouriteJob::class);
    }
    public function favouriteJobs()
    {
        return $this->belongsToMany(Job::class, 'favourite_jobs', 'user_id', 'job_id')
            ->withTimestamps();
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }


}