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
    public function sendInvite(Request $request)
{
    $event = Event::findOrFail($request->input('id_event'));

    if (!$event) {
        return response()->json(['error' => 'Event not found'], 404);
    }

    if (Auth::check()) {
        $user = User::findOrFail(Auth::user()->id);
    }
    else {
        return response()->json(['error' => 'Need to be logged in'], 401);
    }

    $userToInvite = User::where('email', $request->input('email'))->first();

    if (!$userToInvite) {
        return response()->json(['error' => 'User not found'], 404);
    }

    $date = date('Y-m-d H:i:s');
    $notification = Notification::create([
        'date' => $date,
        'text' => "You've been invited to " . $event->name . " by " . $user->username,
        'link' => "/event\/" . $event->id,
        'id_event' => $event->id,
        'id_user' => $userToInvite->id
    ]);


    Invite::create([
        'id_eventnotification' => $notification->id,
        'id_user' => $user->id
    ]);

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
