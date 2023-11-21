<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        return view('pages.events.create');
    }

    public function store(Request $request)
    {
        $id = Auth::user()->id; 
        $user = User::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required',
            'time' => 'required',
            'description' => 'required|string|max:5000',
            'price' => 'required|numeric',
            'capacity' => 'required|numeric',
            'id_location' => 'required|numeric'
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
        if(!Auth::check() && $event->public == false){ //por mensagem de erro dizer que é preciso estar logado para ver o evento
            return redirect()->route('login');
        }
        return view('pages.events.show', ['event' => $event]);
    }

    public function edit(string $id)
    {   
        $event = Event::findOrFail($id);
        $this->authorize('edit', $event);
        return view('pages.events.edit', ['event' => $event]);
    }

    public function update(Request $request, $id)
    {
        $event = Event::find($id);
        $request->validate([
            'name' => 'required',
            'eventdate' => 'required',
            'description' => 'required',
            'price' => 'required',
            'capacity' => 'required',
            'id_location' => 'required',
        ]);
        $this->authorize('update', $event);
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

    public function eventsSearch(Request $request)
    {
        $input = $request->get('search') ? "'" . $request->get('search') . ":*'" : "'*'";
        $events = Event::select()
            ->whereRaw("tsvectors @@ to_tsquery(?)", [$input])
            ->orderByRaw("ts_rank(tsvectors, to_tsquery(?)) ASC", [$input])
            ->get();
        return view('pages.events.search', ['events' => $events]);
    }

    public function joinEvent(string $id)
    {
        $user = User::find(Auth::user()->id);
        $event = Event::findOrFail($id);

        //$this->authorize('join', $event); fazer esta depois

        $user->events()->attach($event->id, ['date' => date('Y-m-d H:i:s')]);

        return redirect()->route('event.show', ['id' => $event->id])
            ->withSuccess('You have successfully joined the event!');
    }
}
