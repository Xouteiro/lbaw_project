<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class EventPolicy
{
    public function view(User $user, Event $event): bool
    {
        if ($event->public) return true;
        else {
            if ($user) {
                if ($event->id_owner == $user->id || $user->admin) return true;
                else {
                    
                }
            }
            return false;
        }
    }

    public function update(User $user, Event $event): bool
    {
        if (Auth::check()) {
            if (($event->id_owner == $user->id && !$user->blocked) || $user->admin) return true;
        }
        return false;
    }

    public function edit(User $user, Event $event): bool
    {
        if (Auth::check()) {
            if (($event->id_owner == $user->id && !$user->blocked) || $user->admin) return true;
        }
        return false;
    }

    public function participants(User $user, Event $event): bool
    {

        $participants = $event->participants()->get();
        foreach ($participants as $participant) {
            if ($participant->id == $user->id) return true;
        }
        if (Auth::check()) {
            if (($event->id_owner == $user->id && !$user->blocked) || $user->admin) return true;
        }
        return false;
    }

    public function removeparticipant(User $user, Event $event): bool
    {
        if (Auth::check()) {
            if (($event->id_owner == $user->id && !$user->blocked) || $user->admin) return true;
        }
        return false;
    }


    public function delete(User $user, Event $event): bool
    {
        if (($event->id_owner == $user->id && !$user->blocked) || $user->admin) return true;
        return false;
    }

    public function join(User $user, Event $event)
    {
        $invites = Notification::where('event_notification.id_user', $user->id)
                ->join('invite',
                'invite.id_eventnotification', '=', 'event_notification.id')
                ->get();

        if ($event->public && $event->openTojoin && !$user->blocked) return true;
        else if ($user->admin) return false;
        else{
            if ($event->id_owner == $user->id) return true;
            else {
                if (!$invites->where('id_event', $event->id)->isEmpty()) {
                    return true;
                }
            }
        }
        return false;
    }

    public function leave(User $user, Event $event) : bool {
        return $user->events->contains($event);
    }
}
