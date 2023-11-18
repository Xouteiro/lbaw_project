<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserPolicy
{
    public function show(User $user)
    {
      return ($user->blocked == FALSE) && (Auth::check());
    }

    public function edit(User $auth, User $user)
    {
      return $auth->id == $user->id;
    }

    public function update(User $auth, User $user)
    {
        return $auth->id == $user->id;
    }
}
