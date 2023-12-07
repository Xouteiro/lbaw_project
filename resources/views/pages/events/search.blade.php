@extends('layouts.nothome')

@section('content')
<div class="back-button-container">
<!--<a class = 'back-button' href="{{ route('events') }}"><img class= 'back-button' src="{{ asset('icons/back.png') }}" alt="Back button"></a>-->
</div>
    <div class="container">
        <h1>Search Results:</h1>
        @if(!$events->count())
            <p>No events found for '{{$search}}'</p>
        @else
        <form id="searchForm" action="{{ route('events.search') }}" method="GET">
            <input name="search" value="" placeholder="Search again" class="search-event"/>
            <button type="submit" id="searchButton">Search</button>
        </form>
        <h3>Events found for '{{$search}}' :</h3>
        <div class="events-container">
            @each('partials.event_card', $events, 'event')
        </div>
        @endif
    </div>
@endsection
