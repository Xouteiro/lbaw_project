@if (($event->public || Auth::check()) && !$event->hide_owner) 
    <div id="event-{{ $event->id }}" class="event-card">
        <a href="{{ route('event.show', ['id' => $event->id]) }}">
            <img src="{{ asset('images/event_default.png') }}" alt="Event Image" class="event-image">
            <div class="event-info">
                <h3>{{ $event->name }}</h3>
                <p>{{ $event->description }}</p>
                <p> Date: {{ $event->eventdate }}</p>
            </div>
        </a>
    </div>
@endif