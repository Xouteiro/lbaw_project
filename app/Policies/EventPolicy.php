<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class EventPolicy
{
    public function view(User $user, Event $event): bool
    {
        if ($event->public) return true;
        else {
            if ($user) {
                if ($event->id_owner === $user->id || $user->admin) return true;
                else {
                    //TODO CHECK IF USER WAS INVITED
                }
            }
            return false;
        }
    }

    public function update(User $user, Event $event): bool
    {
        if (Auth::check()) {
            if ($event->id_owner === $user->id || $user->admin) return true;
        }
        return false;
    }

    public function edit(User $user, Event $event): bool
    {
        if (Auth::check()) {
            if ($event->id_owner === $user->id || $user->admin) return true;
        }
        return false;
    }

    public function participants(User $user, Event $event): bool
    {
        if (Auth::check()) {
            if ($event->id_owner === $user->id || $user->admin) return true;
        }
        return false;
    }

    public function removeparticipant(User $user, Event $event): bool
    {
        if (Auth::check()) {
            if ($event->id_owner === $user->id || $user->admin) return true;
        }
        return false;
    }


    public function delete(User $user, Event $event): bool
    {
        if ($event->id_owner === $user->id || $user->admin) return true;
        return false;
    }

    public function join(User $user, Event $event)
    {
        if ($event->public && $event->openToJoin) return true;
        else if ($user) {
            if ($event->id_owner === $user->id) return true;
            else {
                //TODO CHECK IF USER WAS INVITED
            }
        }
        return false;
    }

    
    
}
