<?php

namespace App\Policies;

use App\Models\Invite;
use App\Models\User;
use App\Models\Event;
use Illuminate\Auth\Access\Response;

class InvitePolicy
{
    public function store(Event $event, User $user) :bool
    {
        return $event->public;
    }
}
