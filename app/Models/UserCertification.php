<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCertification extends Model
{
    protected $fillable = ['user_id', 'name', 'issuing_organization', 'issue_date', 'expiration_date', 'credential_id', 'credential_url'];

    protected $casts = [
        'issue_date' => 'date',
        'expiration_date' => 'date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
