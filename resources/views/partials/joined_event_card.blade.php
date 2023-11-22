@if ($event->public || Auth::check())
    <div id="event-{{ $event->id }}" class="event-card">
        <a href="{{ route('event.show', ['id' => $event->id]) }}">
            @if ($event->getOriginal('pivot_highlighted'))
            <img src="{{ asset('icons/pin.png') }}" alt="Pin Icon" class="event-pin">
            @endif
            @if ($event->getOriginal('pivot_hidden'))
            <p class="event-hidden">Hidden</p>
            @endif
            <img src="/images/event_default.png" alt="Event Image" class="event-image" />
            <div class="event-info">
                <h3>{{ $event->name }}</h3>
                <p> {{ $event->description }}</p>
                <p>{{ $event->eventdate }}</p>
            </div>
        </a>
        @if (Auth::check() && (Auth::user()->id == $event->id_owner || Auth::user()->id == request()->route('id') || Auth::user()->admin))
        <img src="{{ asset('icons/option.png') }}" alt="Manage Icon" class="event-manage">
        @endif
    </div>
@endif