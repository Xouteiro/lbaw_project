<?php

namespace App\Http\Controllers;

use App\Models\Invite;
use App\Models\Notification;
use App\Models\User;
use App\Models\Event;
use App\Mail\Email;
use App\Http\Controllers\EventController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class InviteController extends Controller
{
    public function sendInvite(Request $request) {
        $event = Event::findOrFail($request->id_event);

        if(Auth::user()->admin){
            return redirect()->route('user.show', ['id' => Auth::user()->id]);
        }

        if (!$event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        if($event->eventdate < date('Y-m-d H:i:s')){
            abort(403, 'This event has already ended!');
        }

        if (Auth::check()) {
            $user = User::findOrFail(Auth::user()->id);
        }
        else {
            return response()->json(['error' => 'Need to be logged in'], 401);
        }

        $userToInvite = User::where('username', $request->username)->first();

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

        $data = array(
            'type' => 'invite-event',
            'name' => $userToInvite->name,
            'event' => $event->name,
            'eventId' => $event->id,
            'inviteId' => $invite->id_eventnotification,
        );

        Mail::to($userToInvite->email, $userToInvite->name)->send(new Email($data));

        return back()->with('success', 'Invitation sent successfully!');
    }

    public function acceptInvite(Request $request) {
        if(Auth::user()->admin){
            return redirect()->route('user.show', ['id' => Auth::user()->id]);
        }
        $invite = Notification::findOrFail($request->id_invite);
        $event = $invite->event->id;
        if(isset($request->deny)){
            Invite::where('id_eventnotification', $invite->id)->delete();
            $invite->delete();
            return redirect()->route('user.show', ['id' => Auth::user()->id])
            ->withSuccess('You have successfully denied the invite!');
        }else{
            try {
                $redirectResponse = (new EventController())->joinEvent($event);
                Invite::where('id_eventnotification', $invite->id)->delete();
                $invite->delete();
            } catch (\Exception $e) {
                return redirect()->route('event.show', ['id' => $event]);
            }
        }
        return $redirectResponse;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invite $invite)
    {
        $temp = $invite->id_eventnotification;
        $invite->delete();
        Notification::findOrFail($temp)->delete();
        return redirect()->route('event.show', ['id' => $invite->event->id])
        ->withSuccess('You have successfully deleted your invite!');
    }
}
