@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>{{ $user->username }}</h1>
        @if (Auth::check() && Auth::user()->id == $user->id)
            <a href="{{ route('user.edit', ['id' => $user->id]) }}">Edit Profile</a>
        @endif
        <p>{{ $user->description }}</p>
        <a class="button" href="{{ route('event.create') }}">Create Event</a>
        <h2>Created Events</h2>
        <div class="events-container">
        @each('partials.event_card', $user->ownedEvents, 'event')
        </div>
    </div>
@endsection
