<?php

namespace App\Http\Controllers;

use App\Models\RequestToJoin;
use App\Models\Event;
use App\Models\User;
use App\Models\Notification;
use App\Mail\Email;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class RequestToJoinController extends Controller
{
    public function sendRequestToJoin(Request $request) {
        $event = Event::findOrFail($request->id_event);

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

        $userToRequest = User::findOrFail($event->id_owner);

        if (!$userToRequest) {
            abort(404, 'Event owner not found!');
        }

        if($event->owner->id == $user->id) {
            abort(403, 'Cannot request to join your own event!');
        }

        $checkIfUserAlreadyIn = $event->participants()->where('id_user', $user->id)->first();
        if($checkIfUserAlreadyIn) {
            abort(403, 'You\'re already in this event!');
        }

        $checkIfAlreadyExists = Notification::where([['id_user', $userToRequest->id], ['id_event', $event->id]])->first();
        if($checkIfAlreadyExists) {
            abort(403, 'You already requested to join this event!');
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

        $data = array(
            'type' => 'request-to-join-event',
            'name' => $userToRequest->name,
            'event' => $event->name
        );

        Mail::to($userToRequest->email, $userToRequest->name)->send(new Email($data));

        return response()->json('Request to join sent successfully!', 200);
    }

    public function cancelRequestToJoin(Request $request) {
        $requestToJoinNotification = Notification::where('request_to_join.id_user', Auth::user()->id)->where('id_event', $request->id_event)
        ->join('request_to_join', 'event_notification.id', '=', 'request_to_join.id_eventnotification')->first();
        //$this->authorize('cancelRequestToJoin', $requestToJoin);
        RequestToJoin::findOrFail($requestToJoinNotification->id)->delete();
        Notification::findOrFail($requestToJoinNotification->id)->delete();
        return response()->json('Request to join cancelled successfully!', 200);
    }

    public function acceptRequestToJoin(Request $request) {
        $requestToJoinNotification = Notification::findOrFail($request->id_requestToJoin);
        //$this->authorize('acceptRequestToJoin', $requestToJoinNotification);
        $event = $requestToJoinNotification->event;
        $requestToJoin = RequestToJoin::findOrFail($requestToJoinNotification->id);

        $user = User::find($requestToJoin->id_user);
        $user->events()->attach($event->id, ['date' => date('Y-m-d H:i:s')]);

        $data = array(
            'type' => 'accept-request-to-join-event',
            'name' => $user->name,
            'event' => $event->name,
            'eventId' => $event->id
        );

        Mail::to($user->email, $user->name)->send(new Email($data));

        $requestToJoin->delete();
        $requestToJoinNotification->delete();

        return response()->json('You have successfully accepted the request to join!', 200);
    }

    public function denyRequestToJoin(Request $request){
        $requestToJoinNotification = Notification::findOrFail($request->id_requestToJoin);
        //$this->authorize('denyRequestToJoin', $requestToJoin);
        $requestToJoin = RequestToJoin::findOrFail($requestToJoinNotification->id);
        $event = $requestToJoinNotification->event;
        $user = User::find($requestToJoin->id_user);

        $data = array(
            'type' => 'deny-request-to-join-event',
            'name' => $user->name,
            'event' => $event->name
        );

        Mail::to($user->email, $user->name)->send(new Email($data));

        $requestToJoin->delete();
        $requestToJoinNotification->delete();

        return response()->json('You have successfully denied the request to join!', 200);
    }
}
