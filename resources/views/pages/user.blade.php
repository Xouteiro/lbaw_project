@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>{{ $user->username }}</h1>
        @if (Auth::check() && Auth::user()->id == $user->id)
            <a href="{{ url('/user/' . Auth::user()->id) .'/edit'}}">Edit Profile</a>
        @endif
        <p>{{ $user->description }}</p>
    </div>
    <a class="button" href="{{ url('/event/create') }}">Create Event</a>

    <div class="container">
        <h2>Events</h2>
        @foreach ($user->ownedEvents as $event)
            <div class="event">
                <h3>{{ $event->name }}</h3>
                <p>Description:{{ $event->description }}</p>
                <p>Date:{{ $event->eventdate }}</p>
                <p>Price: {{ $event->price}}</p>
                <p>Capacity:{{ $event->capacity }}</p>
                <p>Location:{{ $event->location->name }}</p>
                <a href="{{ url('/event/' . $event->id) }}">View Event</a>
            </div>
        @endforeach
    </div>
    
@endsection
