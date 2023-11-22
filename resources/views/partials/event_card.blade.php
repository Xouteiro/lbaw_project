@if (($event->public || Auth::check()) && !$event->hide_owner) 
    <div id="event-{{ $event->id }}" class="event-card">
        <a href="{{ route('event.show', ['id' => $event->id]) }}">
            <h3>{{ $event->name }}</h3>
            <p> {{ $event->description }}</p>
        </a>
        @if (Auth::check() && (Auth::user()->id == $event->id_owner || Auth::user()->admin))
        <img src="{{ asset('icons/option.png') }}" alt="Manage Icon" class="event-manage">
        @endif
    </div>
@endif