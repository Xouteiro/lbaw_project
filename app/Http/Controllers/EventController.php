<?php

namespace App\Http\Controllers;

use App\Models\Event;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::paginate(10);
        return view('pages.events.index', ['events' => $events]);
    }


    public function indexAjax()
    {
        $events = Event::paginate(10);
        return response()->json(['events' => $events]);
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        //$this->authorize('create', $user);
        return view('pages.events.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        //$this->authorize('create');
        $request->validate([
            'name' => 'required',
            'date' => 'required',
            'time' => 'required',
            'description' => 'required',
            'price' => 'required',
            'public' => 'required',
            'opentojoin' => 'required',
            'capacity' => 'required',
            'id_location' => 'required'
        ]);

        $eventdate = $request->input('date') . ' ' . $request->input('time') . ':00';

        Event::create([
            'name' => $request->input('name'),
            'eventdate' => $eventdate,
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'public' => $request->input('public'),
            'opentojoin' => $request->input('opentojoin'),
            'capacity' => $request->input('capacity'),
            'id_user' => $user->id,
            'id_location' => $request->input('id_location')
        ]);


        return redirect()->route('user.show', ['id' => $user->id])
            ->withSuccess('You have successfully created your event!');
    }

    public function show(string $id)
    {
        $event = Event::findOrFail($id);
        $this->authorize('view', Auth::user(), $event);
        return view('pages.events.show', ['event' => $event]);
    }

    public function edit(Event $event)
    {
        $this->authorize('update', Auth::user(), $event);
        return view('pages.events.edit', ['event' => $event]);
    }

    public function update(Request $request, $id)
    {
        $event = Event::find($id);
        $request->validate([
            'name' => 'required|string',
            'eventDate' => 'required|date',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'public' => 'required|boolean',
            'opentoJoin' => 'required|boolean',
            'capacity' => 'required|numeric',
            'id_location' => 'required|string',
            //'eventTags' => 'json' //TODO tenho que fazer algo com isso?
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
    public function eventsSearch(Request $request)
    {
        $input = $request->get('search') ? "'" . $request->get('search') . ":*'" : "'*'";
        $events = Event::select()
            ->whereRaw("tsvectors @@ to_tsquery(?)", [$input])
            ->orderByRaw("ts_rank(tsvectors, to_tsquery(?)) ASC", [$input])
            ->get();
        return view('pages.events.search', ['events' => $events]);
    }
}
