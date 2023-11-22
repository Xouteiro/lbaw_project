@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Search Results:</h1>
        @if(!$events->count())
            <p>No events found for {{$search}}</p>
        @else
        <div class="events-container">
            <h3>Events found for {{$search}}</h3>
            @each('partials.event_card', $events, 'event')
        </div>
        @endif
    </div>
@endsection
