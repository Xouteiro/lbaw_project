@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Search Results:</h1>
        <div class="events">
        <?php foreach ($events as $event) { 
            if($event->public) { ?>
            <div class="event-card">
                <a href="/events/{{$event->id}}">
                    <h3>{{ $event->name }}</h3>
                    <p>Location: {{ $event->location->name }}</p>
                    <p>Creator: {{ $event->owner->name }}</p>
                </a>
            </div>
        <?php }} ?>
        </div>
    </div>
@endsection
