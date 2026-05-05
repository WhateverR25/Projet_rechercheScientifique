<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class AdminUserProvider implements UserProvider
{
    public function retrieveById($identifier)
    {
        if ($identifier === env('ADMIN_EMAIL')) {
            $admin = new Admin();
            $admin->email = env('ADMIN_EMAIL');
            $admin->password = env('ADMIN_PASSWORD');
            return $admin;
        }
        return null;
    }

    public function retrieveByToken($identifier, $token)
    {
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
    }

    public function retrieveByCredentials(array $credentials)
    {
        if (isset($credentials['email']) && $credentials['email'] === env('ADMIN_EMAIL')) {
            $admin = new Admin();
            $admin->email = env('ADMIN_EMAIL');
            $admin->password = env('ADMIN_PASSWORD');
            return $admin;
        }
        return null;
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return $user->email === $credentials['email'] && env('ADMIN_PASSWORD') === $credentials['password'];
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
        // No rehashing needed
    }
}
