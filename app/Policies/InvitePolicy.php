<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Event;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class InvitePolicy
{
    public function store(Event $event, User $user) :bool
    {
        return $event->public;
    }

    public function acceptInvite(User $user, Notification $invite) : bool
    {
        return $user->id === $invite->recievedBy->id;  // não funciona ??
    }

}
