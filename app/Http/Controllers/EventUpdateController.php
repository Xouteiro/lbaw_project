<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventUpdate;
use App\Models\Notification;
use App\Mail\Email;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EventUpdateController extends Controller
{
    public function sendEventUpdate($id) {
        $event = Event::findOrFail($id);
        $users = $event->participants()->get();

        if (!$event) {
            return response()->json(['error' => 'Event not found'], 404);
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

            $requestToJoin = new EventUpdate();
            $requestToJoin->notification()->associate($notification);
            $requestToJoin->save();

            $data = array(
                'type' => 'event-update',
                'name' => $user->name,
                'event' => $event->name,
                'eventId' => $event->id
            );

            Mail::to($user->email, $user->name)->send(new Email($data));
        }

        return redirect()->route('event.show', ['id' => $event->id]);
    }

    public function clearEventUpdate(Request $request){
        $eventUpdate = Notification::findOrFail($request->id_eventUpdate);
        EventUpdate::where('id_eventnotification', $eventUpdate->id)->delete();
        $eventUpdate->delete();
        return response()->json('You have successfully cleared the event update notification!', 200);
    }
}
