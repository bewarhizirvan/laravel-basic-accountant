<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property mixed $name
 * @property mixed $email
 * @property mixed $password
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function profiles()
    {
        return $this->belongsToMany('App\Models\Profile')->withTimestamps();
    }

    public function assignProfile($profile)
    {
        return $this->profiles()->sync($profile);
    }

    public function permissions()
    {
        return $this->profiles->map->permissions->flatten()->pluck('name')->unique();
    }

    public function hasPerm($perm, bool $noSuper = false)
    {
        $perms = $this->profiles->map->permissions->flatten()->pluck('name')->unique();
        if($noSuper)
            return $perms->contains($perm);
        else
            return $perms->contains($perm) || $this->getAuthIdentifier() == config('perm.superAdmin');
    }

}
