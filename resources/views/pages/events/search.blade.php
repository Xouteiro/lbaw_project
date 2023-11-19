@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Search Results:</h1>
        <div class="events">
            @each('partials.event_card', $events, 'event')
        </div>
    </div>
@endsection
