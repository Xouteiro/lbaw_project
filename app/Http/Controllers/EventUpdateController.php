<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventUpdate;
use App\Models\Notification;
use App\Models\User;
use App\Mail\Email;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class EventUpdateController extends Controller
{
    public function sendEventUpdate(Request $request) {
        if(Auth::check()){
            $user = User::findOrFail(Auth::user()->id);
            if($user->blocked){
                return redirect()->route('home');
            }
        }

        $event = Event::findOrFail($request->id_event);
        $users = $event->participants()->get();
        $whatChanged = json_decode($request->whatChanged, true);

        if (!$event) {
            return abort(404, 'Event not found!');
        }

        foreach ($users as $user) {
            $notification = Notification::create([
                'date' => date('Y-m-d H:i:s'),
                'text' => "The event " . $event->name . " has been updated!",
                'link' => "/event/" . $event->id
            ]);
            $notification->recievedBy()->associate($user);
            $notification->event()->associate($event);
            $notification->save();

            $eventUpdate = new EventUpdate();
            $eventUpdate->notification()->associate($notification);
            $eventUpdate->what_changed = json_encode($whatChanged);
            $eventUpdate->save();

            $data = array(
                'type' => 'event-update',
                'name' => $user->name,
                'event' => $event->name,
                'eventUpdateId' => $eventUpdate->id_eventnotification,
                'whatChanged' => $whatChanged
            );

            Mail::to($user->email, $user->name)->send(new Email($data));
        }

        return response()->json('You have successfully updated the event!', 200);
    }

    public function clearEventUpdate(Request $request){
        if(Auth::check()){
            $user = User::findOrFail(Auth::user()->id);
            if($user->blocked){
                return redirect()->route('home');
            }
        }
        $eventUpdate = Notification::findOrFail($request->id_eventUpdate);
        EventUpdate::where('id_eventnotification', $eventUpdate->id)->delete();
        $eventUpdate->delete();
        return response()->json('You have successfully cleared the event update notification!', 200);
    }
}
