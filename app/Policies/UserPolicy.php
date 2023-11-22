<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
  public function edit(User $auth, User $user)
  {
    return $auth->id == $user->id || $auth->admin;
  }

  public function update(User $auth, User $user)
  {
    return $auth->id == $user->id || $auth->admin;
  }
}
