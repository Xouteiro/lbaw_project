@extends('layouts.app')

@section('content')
    <div class="container">
        <?php foreach ($events as $event) { 
            if($event->public) { ?>
            <div class="event-card">
                <a href="/events/{{$event->id}}">
                    <h4>{{ $event->name }}</h4>
                    <p>Location: {{ $event->location->name }}</p>
                    <p>Creator: {{ $event->owner->name }}</p>
                </a>
            </div>
        <?php }} ?>
    </div>
@endsection
