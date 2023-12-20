<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use App\Models\Notification;
use App\Mail\Email;
use App\Models\EventUpdate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EventController extends Controller
{
    public function index()
    {        
        return view('pages.events.search');
    }

    public function indexAjax(Request $request)
    {
        $datefilter = $request->has('date') ? $request->get('date') : null;
        $locationfilter = $request->has('id_location') ? $request->get('id_location') : null;
        $freefilter = $request->has('free') ? $request->get('free') : null;
        $finishedfilter = $request->has('finished') ? $request->get('finished') : null;
        $openToJoinfilter = $request->has('opentojoin') ? $request->get('opentojoin') : null;


        $order = $request->has('order') ? $request->get('order') : null;
        if($order !== null){
        $orderType = explode('-', $order)[0];
        $orderDirection = explode('-', $order)[1];
        }


        $userId = Auth::user()->id;
        $user = User::findOrFail($userId);
        if ($user->is_admin) {
            $allEvents = Event::get();
        } else {
            $allEvents = Event::where(function ($query) use ($userId) {
                $query->where('hide_owner', false)
                      ->where('public', true)
                      ->orWhere(function ($query) use ($userId) {
                          $query->where('public', false)
                                ->whereHas('participants', function ($query) use ($userId) {
                                    $query->where('id_user', $userId);
                                });
                      });
            });
        }

        if ($request->get('search') == null && $datefilter == null && $locationfilter == null && $freefilter == null && $finishedfilter == null && $openToJoinfilter == null) { //sem filtros
            if ($order !== null) {
                $events = $allEvents->select()
                                    ->where('eventdate', '>=', Carbon::now())
                                    ->orderBy($orderType, $orderDirection)
                                    ->paginate(10);
            } else {
                $events = $allEvents->select()
                                    ->where('eventdate', '>=', Carbon::now())
                                    ->paginate(10);
            }
            return  response()->json(['events' => $events, 'search' => $request->get('search')]);
        }

        if ($datefilter !== null || $locationfilter !== null || $request->get('search') !== null || $freefilter !== null || $finishedfilter !== null || $openToJoinfilter !== null) { //com filtros
            $query = $allEvents->select();

            $query->where(function ($query) use ($datefilter, $locationfilter, $request, $freefilter, $finishedfilter, $openToJoinfilter) {
                if ($request->get('search') !== null) {
                    $query->whereRaw("tsvectors @@ to_tsquery(?, ?)", ['english', $request->get('search')])
                    ->orderByRaw("ts_rank(tsvectors, to_tsquery(?, ?)) ASC", ['english', $request->get('search')]);
              

                }
                if ($datefilter !== null) {
                    $query->where('eventdate', '>=', $datefilter);
                }

                if ($locationfilter !== null) {
                    $query->where('id_location', '=', $locationfilter);
                }
                if ($freefilter !== null) {
                    $query->where('price', '=', 0);
                }
                if ($finishedfilter !== null) {
                    $query->where('eventdate', '<', Carbon::now());
                }
                if ($openToJoinfilter !== null) {
                    $query->where('opentojoin', '=', true);
                }
            });

            if ($order !== null) {
                $events = $query->orderBy($orderType, $orderDirection === 'asc' ? 'asc' : 'desc')->paginate(10);
            } else {
                $events = $query->paginate(10);
            }
            return  response()->json(['events' => $events, 'search' => $request->get('search')]);
        }
    }

    public function create()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        if(Auth::user()->is_admin){
            return redirect()->route('home');
        }
        return view('pages.events.create');
    }

    public function store(Request $request)
    {
        $id = Auth::user()->id;
        User::findOrFail($id);
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
        ], [
            'date.after_or_equal' => 'Event date must be in the future.',
            'price' => 'Price must 0 or more.',
            'capacity' => 'Capacity must be 0 or more.',
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
            'id_owner' => $id,
            'id_location' => $request->input('id_location')
        ]);

        return redirect()->route('user.show', ['id' => $id])
            ->withSuccess('You have successfully created your event!');
    }

    public function show(Request $request, string $id)
    {
        $event = Event::findOrFail($id);

        if ($request->id_invite) {
            $invite = Notification::findOrFail($request->id_invite);
            return view('pages.events.show', ['event' => $event, 'invite' => $invite]);
        }

        if ($request->id_requestToJoin) {
            $requestToJoin = Notification::findOrFail($request->id_requestToJoin);
            return view('pages.events.show', ['event' => $event, 'requestToJoin' => $requestToJoin]);
        }

        if($request->id_eventUpdate){
            $eventUpdate = EventUpdate::find($request->id_eventUpdate);
            if($eventUpdate === null){
                abort(404, "Event update not found!"); // a mensagem de erro não está a ser displayed?? 
            }
            return view('pages.events.show', ['event' => $event, 'whatChanged' => json_decode($eventUpdate->what_changed)]);
        }

        if (!Auth::check() && $event->public == false) { //por mensagem de erro dizer que é preciso estar logado para ver o evento
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

        $this->authorize('update', $event);

        $whatChanged = array();
        if ($event->name != $request->input('name')) {
            $whatChanged['old_name'] = $event->name;
            $whatChanged['name'] = $request->input('name');
            $event->name = $request->input('name');
        }
        $date = date('Y-m-d H:i:s', strtotime($request->input('eventdate')));
        if ($event->eventdate != $date) {
            $whatChanged['old_eventdate'] = $event->eventdate;
            $whatChanged['eventdate'] = $date;
            $event->eventdate = $date;
        }
        if ($event->description != $request->input('description')) {
            $whatChanged['old_description'] = $event->description;
            $whatChanged['description'] = $request->input('description');
            $event->description = $request->input('description');
        }
        if ($event->price != $request->input('price')) {
            $whatChanged['old_price'] = $event->price;
            $whatChanged['price'] = $request->input('price');
            $event->price = $request->input('price');
        }
        if ($event->public != $request->input('public')) {
            $whatChanged['old_public'] = $event->public;
            $whatChanged['public'] = $request->input('public');
            $event->public = $request->input('public');
        }
        if ($event->opentojoin != $request->input('opentojoin')) {
            $whatChanged['old_opentojoin'] = $event->opentojoin;
            $whatChanged['opentojoin'] = $request->input('opentojoin');
            $event->opentojoin = $request->input('opentojoin');
        }
        if ($event->capacity != $request->input('capacity')) {
            $whatChanged['old_capacity'] = $event->capacity;
            $whatChanged['capacity'] = $request->input('capacity');
            $event->capacity = $request->input('capacity');
        }
        if ($event->id_location != $request->input('id_location')) {
            $whatChanged['old_id_location'] = $event->id_location;
            $whatChanged['id_location'] = $request->input('id_location');
            $event->id_location = $request->input('id_location');
        }
        if(!empty($whatChanged))
        {
            $event->save();
            return (new EventUpdateController())->sendEventUpdate($id, $whatChanged);
        }
        return redirect()->route('event.show', ['id' => $event->id]);
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
        $this->authorize('removeparticipant', $event);
        $event->participants()->detach($id_participant);
        return response()->json(['message' => 'Participant removed'], 200);
    }

    public function delete(string $id)
    {
        $event = Event::find($id);
        $this->authorize('delete', $event);
        $users = $event->participants()->get();

        foreach ($users as $user) {
            $data = array(
                'type' => 'cancel-event',
                'name' => $user->name,
                'event' => $event->name
            );

            Mail::to($user->email, $user->name)->send(new Email($data));
        }
        $event->delete();

        return response()->json(['message' => 'Delete successful'], 200);
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
        return view('pages.events.search', [$request]);
    }


    public function joinEvent(string $id)
    {
        if(Auth::user()->is_admin){
            return redirect()->route('home');
        }
        $user = User::find(Auth::user()->id);
        $event = Event::findOrFail($id);

        if($event->eventdate < date('Y-m-d H:i:s')){
            abort(403, 'This event has already ended!');
        }

        $this->authorize('join', $event);

        $user->events()->attach($event->id, ['date' => date('Y-m-d H:i:s')]);

        $data = array(
            'type' => 'join-event',
            'name' => $user->name,
            'event' => $event->name,
            'eventId' => $event->id
        );

        Mail::to($user->email, $user->name)->send(new Email($data));

        return redirect()->route('event.show', ['id' => $event->id]);
    }

    public function leaveEvent(string $id)
    {
        $user = User::find(Auth::user()->id);
        $event = Event::findOrFail($id);

        $this->authorize('leave', $event);

        $event->participants()->detach($user->id);

        $data = array(
            'type' => 'leave-event',
            'name' => $user->name,
            'event' => $event->name,
            'eventId' => $event->id
        );

        Mail::to($user->email, $user->name)->send(new Email($data));

        return redirect()->route('event.show', ['id' => $event->id]);
    }
}
