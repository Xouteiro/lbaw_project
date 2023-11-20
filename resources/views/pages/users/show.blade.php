@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>{{ $user->username }}</h1>
        <p>{{ $user->description }}</p>
        <a class="button" href="{{ route('event.create') }}">Create Event</a>
        @if (Auth::check() && Auth::user()->id == $user->id)
            <a class="button" href="{{ url('/user/' . Auth::user()->id .'/edit')}}">Edit Profile</a>
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
    </div>
@endsection
