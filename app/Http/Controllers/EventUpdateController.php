<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventUpdate;
use App\Models\Notification;
use Illuminate\Http\Request;

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
        }

        return redirect()->route('event.show', ['id' => $event->id]);
    }

    public function clearEventUpdate(Request $request){
        $eventUpdate = Notification::findOrFail($request->id_eventUpdate);
        //$this->authorize('clearEventUpdate', $eventUpdate);
        EventUpdate::where('id_eventnotification', $eventUpdate->id)->delete();
        $eventUpdate->delete();
        return response()->json('You have successfully cleared the event update notification!', 200);
    }
}
