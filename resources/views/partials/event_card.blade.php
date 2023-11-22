@if (url()->current() == route('events'))
    @if ($event->public && !$event->hide_owner) <!-- Make private events visible for authenticated users -->
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
@elseif (url()->current() == route('user.show', ['id' => request()->route('id')]))
    @if ($event->public || Auth::check()) <!-- Make private events visible for authenticated users -->
        <div id="event-{{ $event->id }}" class="event-card">
            <a href="{{ route('event.show', ['id' => $event->id]) }}">
                @if ($event->highlight_owner)
                <img src="{{ asset('icons/pin.png') }}" alt="Pin Icon" class="event-pin">
                @endif
                @if ($event->hide_owner)
                <p class="event-hidden">Hidden</p>
                @endif
                <h3>{{ $event->name }}</h3>
                <p> {{ $event->description }}</p>
            </a>
            @if (Auth::check() && (Auth::user()->id == $event->id_owner || Auth::user()->admin))
            <img src="{{ asset('icons/option.png') }}" alt="Manage Icon" class="event-manage">
            @endif
        </div>
    @endif 
@endif
