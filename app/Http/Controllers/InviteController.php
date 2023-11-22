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
            return back()->withErrors([
                'invite' => 'User not found!',
            ])->onlyInput('invite');
        }

        if($event->owner->id == $userToInvite->id) {
            return back()->withErrors([
                'invite' => 'Cannot invite yourself!',
            ])->onlyInput('invite');
        }

        $checkIfUserAlreadyIn = $event->participants()->where('id_user', $userToInvite->id)->first();
        if($checkIfUserAlreadyIn) {
            return back()->withErrors([
                'invite' => 'User already in event!',
            ])->onlyInput('invite');
        }

        $checkIfAlreadyExists = Notification::where([['id_user', $userToInvite->id], ['id_event', $event->id]])->first();
        if($checkIfAlreadyExists) {
            return back()->withErrors([
                'invite' => 'User already has an invite for this event!',
            ])->onlyInput('invite');
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
        //$this->authorize('acceptInvite', $invite);
        $event = $invite->event->id;
        Invite::where('id_eventnotification', $invite->id)->delete();
        $invite->delete();
        if(isset($request->deny)){
            return redirect()->route('event.show', ['id' => $event])
            ->withSuccess('You have successfully denied the invite!');
        }
        return (new EventController())->joinEvent($event);
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
