<?php

namespace App\Http\Controllers;

use App\Models\Invite;
use App\Models\Notification;
use App\Models\User;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InviteController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $event = Event::findOrFail($request->input('event_id'));

        if (!$event) {
            return response()->json(['error' => 'Event not found'], 404);
        }
        $user = 0;
        if(Auth::check()){
            $user = User::findOrFail(Auth::id());
        }else{
            return response()->json(['error' => 'Need to be logged in'],401);
        }

        $this->authorize('store', $event);
        
        $userToInvite = User::where('email', $request->input('email'))->first();

        if(!$userToInvite){
            return response()->json(['error' => 'User not found'],404);
        }
        
        $notification = new Notification();
        $notification->text = "You've been invited to ".$event->name." by ".$user->username;
        $notification->link = "/event\/".$event->id;
        $notification->event()->associate($event);
        $notification->receivedBy()->associate($userToInvite);
        $notification->save();
        
        $invite = new Invite();
        $invite->sentBy()->associate($user);
        $invite->notification()->associate($notification);
        $invite->save();

        $userToInvite->pendingInvites()->attach($invite);
        $userToInvite->save();

        return response()->json(['message' => 'Invitation sent successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invite $invite)
    {
        //
    }
}
