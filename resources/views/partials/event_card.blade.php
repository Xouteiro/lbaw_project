@if (($event->public || Auth::check()) && !$event->hide_owner) 
    <div id="event-{{ $event->id }}" class="event-card">
        <a href="{{ route('event.show', ['id' => $event->id]) }}">
            <img src="{{ $event->getEventImage($event->id) }}" alt="Event Image" class="event-image">
            <div class="event-info">
                <h3>{{ $event->name }}</h3>
                <p>{{ $event->description }}</p>
                <p> Date: {{ $event->eventdate }}</p>
                @if($event->eventdate < date('Y-m-d'))
                <p>Finished</p>
                @elseif($event->eventdate == date('Y-m-d'))
                    <p>Today</p>
                @elseif($event->eventdate > date('Y-m-d'))
                    <p>Upcoming</p>
                @endif
            </div>
        </a>
    </div>
@endif