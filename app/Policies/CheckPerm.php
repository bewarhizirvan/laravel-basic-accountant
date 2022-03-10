<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CheckPerm
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function perm(User $user, Perm $perm)
    {
        return $user->hasPerm($perm->name);
        //return $user->id == config('perm.superAdmin') || $user->hasPerm($perm->name);
    }

    public function can(string $perm)
    {

        return \Auth::user()->hasPerm($perm);
        //return $user->id == config('perm.superAdmin') || $user->hasPerm($perm->name);
    }

    public function limit(User $user, Perm $perm)
    {
        $limit = $perm->name;
        return $user->limits->$limit;
        //return $user->id == config('perm.superAdmin') || $user->hasPerm($perm->name);
    }

}
