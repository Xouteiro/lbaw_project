<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class EventPolicy
{

    public function create(): bool
    {
        if(Auth::check()) return true;
        return false;
    }

    
    public function update(User $user, Event $event): bool
    {
        if($user){
            if($event->owner() === $user->id) return true;
        }
        return false;
    }

    public function delete(User $user, Event $event): bool
    {
        if($event->owner() === $user->id) return true;
        return false;
    }
}
