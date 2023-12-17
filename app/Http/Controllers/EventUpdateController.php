<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventUpdate;
use App\Models\Notification;
use Illuminate\Http\Request;

class EventUpdateController extends Controller
{
    public function sendEventUpdate(Request $request, $id) {
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

    }
}
