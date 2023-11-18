<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class EventPolicy
{
    public function create(): bool{
        return (Auth::check());
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Event $event): bool
    {
        if($event->public) return true;
        else{
            if($user){
                if($event->owner() === $user->id) return true;
                else{
                    //TODO CHECK IF USER WAS INVITED
                }
            }
            return false;
        }
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
