<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $this->authorize('create');
        $user = User::findOrFail(Auth::id());
        return view('pages.event.create');
    }

    /**
     * Store a newly created resource in storage.
     */
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

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $event = Event::findOrFail($id);
        $this->authorize('view', Auth::user(),$event);
        return view('pages.event', ['event' => $event]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        $this->authorize('update', Auth::user(), $event);
        return view('pages.event');
    }

    /**
     * Update the specified resource in storage.
     */
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
        $this->authorize('update',Auth::user(),$event);
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

    /**
     * Remove the specified resource from storage.
     */
    public function delete($id)
    {
        $event = Event::find($id);
        $this->authorize('delete',Auth::user(), $event);
        $event->delete();
        return redirect()->route('event.index')
            ->withSuccess('You have successfully deleted your comment!');
    }
}
