<?php

namespace App\Http\Controllers;

use App\Models\RequestToJoin;
use App\Models\Event;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequestToJoinController extends Controller
{
    public function sendRequestToJoin(Request $request) {
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

        $userToRequest = User::findOrFail($event->id_owner);

        if (!$userToRequest) {
            return back()->withErrors([
                'requestToJoin' => 'User not found!',
            ])->onlyInput('requestToJoin');
        }

        if($event->owner->id == $user->id) {
            return back()->withErrors([
                'requestToJoin' => 'Cannot request to join your own event!',
            ])->onlyInput('requestToJoin');
        }

        $checkIfUserAlreadyIn = $event->participants()->where('id_user', $user->id)->first();
        if($checkIfUserAlreadyIn) {
            return back()->withErrors([
                'requestToJoin' => 'You\'re already in this event!',
            ])->onlyInput('requestToJoin');
        }

        $checkIfAlreadyExists = Notification::where([['id_user', $user->id], ['id_event', $event->id]])->first();
        if($checkIfAlreadyExists) {
            return back()->withErrors([
                'requestToJoin' => 'You already requested to join this event!',
            ])->onlyInput('requestToJoin');
        }

        $notification = Notification::create([
            'date' => date('Y-m-d H:i:s'),
            'text' => $user->username . " has requested to join " . $event->name,
            'link' => "/event/" . $event->id
        ]);
        $notification->recievedBy()->associate($userToRequest);
        $notification->event()->associate($event);
        $notification->save();

        $requestToJoin = new RequestToJoin();
        $requestToJoin->requestedBy()->associate($user);
        $requestToJoin->notification()->associate($notification);
        $requestToJoin->save();

        $user->joinRequests()->save($requestToJoin);

        return back()->with('success', 'Request to join sent successfully!');
    }

    public function acceptRequestToJoin(Request $request) {
        $requestToJoinNotification = Notification::findOrFail($request->id_requestToJoin);
        //$this->authorize('acceptRequestToJoin', $requestToJoinNotification);
        $event = $requestToJoinNotification->event->id;
        $requestToJoin = RequestToJoin::findOrFail($requestToJoinNotification->id);

        $user = User::find($requestToJoin->id_user);
        $user->events()->attach($event, ['date' => date('Y-m-d H:i:s')]);

        $requestToJoin->delete();
        $requestToJoinNotification->delete();

        return redirect()->route('event.show', ['id' => $event]);
    }

    public function denyRequestToJoin(Request $request){
        $requestToJoin = Notification::findOrFail($request->id_requestToJoin);
        //$this->authorize('denyRequestToJoin', $requestToJoin);
        RequestToJoin::where('id_eventnotification', $requestToJoin->id)->delete();
        $requestToJoin->delete();
        return response()->json('You have successfully denied the request to join!', 200);
    }
}
