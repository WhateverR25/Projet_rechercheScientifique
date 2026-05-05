<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Admin extends Authenticatable implements JWTSubject
{
    protected $fillable = ['email', 'password'];

    public function getJWTIdentifier()
    {
        return $this->email;
    }

    public function getJWTCustomClaims()
    {
        return ['role' => 'admin'];
    }

    public function getAuthIdentifierName()
    {
        return 'email';
    }

    public function getAuthIdentifier()
    {
        return $this->email;
    }
}
