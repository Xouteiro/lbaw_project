@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>{{ $user->username }}</h1>
        <p>{{ $user->description }}</p>
        <a class="button" href="{{ route('event.create') }}">Create Event</a>
        @if (Auth::check() && Auth::user()->id == $user->id)
            <a class="button" href="{{ url('/user/' . Auth::user()->id .'/edit')}}">Edit Profile</a>
        @endif
        <h2>Created Events</h2>
        <div class="events-container">
        @each('partials.event_card', $user->ownedEvents, 'event')
        </div>
        <div class="notifications">
            <h2> Notifications</h2>
            <div class="invites">
                @foreach ($user->pendingInvites as $invite)
                    <a class="pending_invite" href="{{url($invite->notification->link)}}">
                        <h3>Sent by: {{$invite->sentBy->name}}</h3>
                        <p> {{$invite->notification->text}}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
@endsection
