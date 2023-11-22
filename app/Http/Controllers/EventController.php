<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class EventController extends Controller
{
    public function index()
    {
        if(Auth::check()){
            $events = Event::where('hide_owner','=', false)->paginate(10);
            return view('pages.events.index', ['events' => $events]);
        }
        else{
            $events = Event::where('public', '=' , true)->paginate(10); 
            return view('pages.events.index', ['events' => $events]);
        }
    }

    public function indexAjax()
    {
        if(Auth::check()){
            $events = Event::where('hide_owner','=', false)->paginate(10);
            return response()->json(['events' => $events]);
        }
        else{
            $events = Event::where('public', '=' , true)->where('hide_owner','=', false)->paginate(10); 
            return response()->json(['events' => $events]);
        }
    }

    public function create(): View
    {
        $this->authorize('create');
        return view('pages.events.create');
    }

    public function store(Request $request)
    {
        $id = Auth::user()->id; 
        $this->authorize('create');
        $user = User::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:100',
            'date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:' . date('Y-m-d'),
            ],
            'time' => 'required',
            'description' => 'required|string|max:5000',
            'price' => 'required|numeric|min:0',
            'capacity' => 'required|numeric|min:0',
            'id_location' => 'required|numeric|min:1',
        ],[
            'date.after_or_equal' => 'Event date must be in the future.',
            'price' => 'Price must 0 or more.',
            'capacity' => 'Capacity must be 0 or more.',
        ]);

        $eventdate = $request->input('date') . ' ' . $request->input('time') . ':00';
        $event = Event::create([
            'name' => $request->input('name'),
            'eventdate' => $eventdate,
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'public' => $request->input('public'),
            'opentojoin' => $request->input('opentojoin'),
            'capacity' => $request->input('capacity'),
            'id_owner' => $id,
            'id_location' => $request->input('id_location')
        ]);

        //$user->events()->attach($event->id, ['date' => date('Y-m-d H:i:s')]); se pusermos por so quando der para dar unjoin se nao parte o delete do evento



        return redirect()->route('user.show', ['id' => $id])
            ->withSuccess('You have successfully created your event!');
    }

    public function show(string $id)
    {
        $event = Event::findOrFail($id);
        $this->authorize('view', $event);

        return view('pages.events.show', ['event' => $event]);
    }

    public function edit(string $id)
    {   
        $event = Event::findOrFail($id);
        $this->authorize('update', $event);
        return view('pages.events.edit', ['event' => $event]);
    }

    public function update(Request $request, $id)
    {
        $event = Event::find($id);
        $request->validate([
            'name' => 'required|max:100',
            'eventdate' => [
                'required',
                'date_format:Y-m-d\TH:i',
                'after_or_equal:' . date(DATE_ATOM),
            ],
            'description' => 'required|max:5000',
            'price' => 'required|numeric|min:0',
            'capacity' => 'required|numeric|min:0',
            'id_location' => 'required|numeric|min:1',
        ], [
            'eventdate.after_or_equal' => 'Event date must be in the future.',
            'price' => 'Price must 0 or more.',
            'capacity' => 'Capacity must be 0 or more.',
        ]);
    

        $event->name = $request->input('name');
        $event->eventdate = $request->input('eventdate');
        $event->description = $request->input('description');
        $event->price = $request->input('price');
        $event->public = $request->input('public');
        $event->opentojoin = $request->input('opentojoin');
        $event->capacity = $request->input('capacity');
        $event->id_owner = $event->id_owner;
        $event->id_location = $request->input('id_location');
        $event->save();
        return redirect()->route('event.show', ['id' => $event->id])
            ->withSuccess('You have successfully edited your profile!');
    }

    public function participants(string $id)
    {
        $event = Event::findOrFail($id);
        $this->authorize('participants', $event);
        return view('pages.events.participants', ['event' => $event]);
    }

    public function removeparticipant(string $id, string $id_participant)
    {
        $event = Event::findOrFail($id);
        $this->authorize('participants', $event);
        $event->participants()->detach($id_participant);
        return redirect()->route('event.participants', ['id' => $event->id])
            ->withSuccess('You have successfully removed the participant!');
    }

    public function delete(string $id)
    {
        $event = Event::find($id);
        $this->authorize('delete', $event);
        $event->participants()->detach();
        $event->delete();
        return redirect()->route('user.show', ['id' => Auth::user()->id])
            ->withSuccess('You have successfully deleted your comment!');
    }

    public function deleteDummy()
    { 
        abort(403, 'This is a great event! Why would you want to do that?');
    }

    public function removeDummy()
    { 
        abort(403, 'This is not your event!');
    }

    public function eventsSearch(Request $request)
    {
        $input = $request->get('search') ? "'" . $request->get('search') . ":*'" : "'*'";
        $events = Event::select()
            ->whereRaw("tsvectors @@ to_tsquery(?)", [$input])
            ->orderByRaw("ts_rank(tsvectors, to_tsquery(?)) ASC", [$input])
            ->get();
        return view('pages.events.search', ['events' => $events, 'search' => $request->get('search')]);
    }

    public function joinEvent(string $id)
    {
        $user = User::find(Auth::user()->id);
        $event = Event::findOrFail($id);

        $this->authorize('join', $event);

        $user->events()->attach($event->id, ['date' => date('Y-m-d H:i:s')]);

        return redirect()->route('event.show', ['id' => $event->id])
            ->withSuccess('You have successfully joined the event!');
    }
}
