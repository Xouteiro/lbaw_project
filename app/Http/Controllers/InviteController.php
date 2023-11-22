<?php

namespace App\Http\Controllers;

use App\Models\Invite;
use App\Models\Notification;
use App\Models\User;
use App\Models\Event;
use App\Http\Controllers\EventController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InviteController extends Controller
{
    public function sendInvite(Request $request) {
        $event = Event::findOrFail($request->id_event);

        if (!$event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        if (Auth::check()) {
            $user = User::findOrFail(Auth::user()->id);
        }
        else {
            return response()->json(['error' => 'Need to be logged in'], 401);
        }

        $userToInvite = User::where('email', $request->email)->first();

        if (!$userToInvite) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $date = date('Y-m-d H:i:s');
        $notification = Notification::create([
            'date' => $date,
            'text' => "You've been invited to " . $event->name . " by " . $user->username,
            'link' => "/event/" . $event->id
        ]);
        $notification->recievedBy()->associate($userToInvite);
        $notification->event()->associate($event);
        $notification->save();

        $invite = new Invite();
        $invite->sentBy()->associate($user);
        $invite->notification()->associate($notification);
        $invite->save();

        $user->pendingInvites()->save($invite);

        return redirect()->route('event.show', ['id' => $event->id])
        ->withSuccess('Invitation sent successfully!');
    }

    public function acceptInvite(Request $request) {
        $invite = Notification::findOrFail($request->id_invite);
        error_log($invite->recievedBy->id === Auth::user()->id);
        //$this->authorize('acceptInvite', $invite);
        $event = $invite->event->id;
        Invite::where('id_eventnotification', $invite->id)->delete();
        $invite->delete();
        return EventController::joinEvent($event);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invite $invite)
    {
        // $this->authorize('delete', $invite);
        $temp = $invite->id_eventnotification;
        $invite->delete();
        Notification::findOrFail($temp)->delete();
        return redirect()->route('event.show', ['id' => $invite->event->id])
        ->withSuccess('You have successfully deleted your invite!');
    }
}
