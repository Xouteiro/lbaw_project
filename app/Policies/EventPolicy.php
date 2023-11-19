<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Auth;


class EventPolicy
{
    public function create(User $auth, User $user ): bool{
        return ($user->id == $auth->id);
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
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

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Event $event): bool
    {
        if($user){
            if($event->owner() === $user->id) return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Event $event): bool
    {
        if($event->owner() === $user->id) return true;
        return false;
    }
}
