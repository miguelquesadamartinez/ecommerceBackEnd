<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalAccessTokens extends Model
{
    protected $table = 'personal_access_tokens';

    protected $fillable = [
        'tokenable',
        'name',
        'token',
        'abilities',
        'last_used_at',
        'expires_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime:Y-m-d H:i:s.v',
        'expires_at' => 'datetime:Y-m-d H:i:s.v',
        'created_at' => 'datetime:Y-m-d H:i:s.v',
        'updated_at' => 'datetime:Y-m-d H:i:s.v',
    ];
}
