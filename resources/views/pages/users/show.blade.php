@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>{{ $user->username }}</h1>
        <p>{{ $user->description }}</p>
        <a class="button" href="{{ route('event.create') }}">Create Event</a>
        @if (Auth::check() && (Auth::user()->id === $user->id || Auth::user()->admin))
            <a class="button" href="{{ url('/user/' . $user->id .'/edit')}}">Edit Profile</a>
        @endif
        <div class="profile-events-title-div">
            <h2 class="joined-events-title active">Joined Events</h2>
            <h2 class="created-events-title">Created Events</h2>
        </div>
        <div class="joined-events-container" style="display: block">
        @each('partials.event_card', $user->events, 'event')
        </div>
        <div class="created-events-container" style="display: none">
        @each('partials.event_card', $user->ownedEvents, 'event')
        </div>
        <div class="notifications">
            <h2>Invites</h2>
            <div class="invites">
                @if($notifications[0]->count() == 0)
                    <h4>No invites</h4>
                @endif
                @foreach($notifications[0] as $invite)
                <a class="pending_invite" href="{{ url($invite->link) . '?id_invite=' . $invite->id}}">
                    <h4>- {{$invite->text}}</h4>
                </a>
                @endforeach
            </div>
            {{-- <h2>Event Updates</h2>
            <div class="event-updates">
                @foreach($notifcations[1] as $eventUpdate)
                <a class="pending_event_update" href="{{ url($eventUpdate->link) . '?id_eventUpdate=' . $eventUpdate->id}}">
                    <h4>- {{$eventUpdate->text}}</h4>
                </a>
                @endforeach
            </div>
            <h2>Requests To Join</h2>
            <div class="requests-to-join">
                @foreach($notifcations[2] as $requestToJoin)
                <a class="pending_request_to_join" href="{{ url($requestToJoin->link) . '?id_requestToJoin=' . $requestToJoin->id}}">
                    <h4>- {{$requestToJoin->text}}</h4>
                </a>
                @endforeach
            </div> --}}
        </div>
    </div>
@endsection
