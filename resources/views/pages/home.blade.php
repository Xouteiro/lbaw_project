@extends('layouts.app')

@section('content')
    <div class="container home-page">
        <h1>Welcome to home page of Invents!</h1>
        <form id="searchForm" action="{{ route('events.search') }}" method="GET">
            <input name="search" value="" placeholder="Search event" class="search-event"/>
            <button type="submit" id="searchButton">Search</button>
            <a class = 'button' href="{{ route('events') }}">Check them all!</a>
        </form>
        <h3>Check out some of the public events</h3>
        <div class="events-container">
            @each('partials.event_card', $events, 'event')
        </div>
    </div>
@endsection
