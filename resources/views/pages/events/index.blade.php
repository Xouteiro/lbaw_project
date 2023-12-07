@extends('layouts.nothome')

@section('content')
    <div class="container">
        <form id="searchForm" action="{{ route('events.search') }}" method="GET">
            <input name="search" value="" placeholder="Search event" class="search-event"/>
            <button type="submit" id="searchButton">Search</button>
        </form>
        <div class="events-container" id="eventsContainer">
            @each('partials.event_card', $events, 'event')
        </div>
    </div>
@endsection
