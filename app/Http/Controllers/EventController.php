<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function index(){
        $events = Event::all();
        return view('pages.events.index', ['events' => $events]);
    }

    public function create(Request $request)
    {
        $this->authorize('create');
        $user = User::findOrFail(Auth::id());
        return view('pages.event.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create');
        $validatedData = $request->validate([
            'name' => 'required|string',
            'eventDate' => 'required|date',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'public' => 'required|boolean',
            'opentoJoin' => 'required|boolean',
            'capacity' => 'required|numeric',
            'id_location' => 'required|string',
            'eventTags' => 'json' //TODO tenho que fazer algo com isso?
        ]);
        $event = new Event();
        $event->name = $request->input('name');
        $event->eventDate = $request->input('eventDate');
        $event->description = $request->input('description');
        $event->price = $request->input('price');
        $event->public = $request->input('public');
        $event->opentojoin = $request->input('opentojoin');
        $event->capacity = $request->input('capacity');
        $event->id_user = $request->input('id_user');
        $event->id_location = $request->input('id_location');
        $event->save();
        return response()->json($event);
    }

    public function show(string $id)
    {
        $event = Event::findOrFail($id);
        $this->authorize('view', Auth::user(), $event);
        return view('pages.event', ['event' => $event]);
    }

    public function edit(Event $event)
    {
        $this->authorize('update', Auth::user(), $event);
        return view('pages.event');
    }

    public function update(Request $request, $id)
    {
        $event = Event::find($id);
        $validatedData = $request->validate([
            'name' => 'required|string',
            'eventDate' => 'required|date',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'public' => 'required|boolean',
            'opentoJoin' => 'required|boolean',
            'capacity' => 'required|numeric',
            'id_location' => 'required|string',
            'eventTags' => 'json' //TODO tenho que fazer algo com isso?
        ]);
        $this->authorize('update', Auth::user(), $event);
        $event->name = $request->input('name');
        $event->eventDate = $request->input('eventDate');
        $event->description = $request->input('description');
        $event->price = $request->input('price');
        $event->public = $request->input('public');
        $event->opentojoin = $request->input('opentojoin');
        $event->capacity = $request->input('capacity');
        $event->id_user = $request->input('id_user');
        $event->id_location = $request->input('id_location');
        $event->save();
        return response()->json($event);
    }

    public function delete($id)
    {
        $event = Event::find($id);
        $this->authorize('delete', Auth::user(), $event);
        $event->delete();
        return redirect()->route('event.index')
            ->withSuccess('You have successfully deleted your comment!');
    }
}
