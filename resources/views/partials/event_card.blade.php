@if (url()->current() == route('events') || url()->current() == route('events.search'))
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
@elseif (url()->current() == route('user.show', ['id' => request()->route('id')]))
    @if ($event->public || Auth::check()) 
        <div id="event-{{ $event->id }}" class="event-card">
            <a href="{{ route('event.show', ['id' => $event->id]) }}">
                @if ($event->highlight_owner || $event->getOriginal('pivot_highlighted'))
                <img src="{{ asset('icons/pin.png') }}" alt="Pin Icon" class="event-pin">
                @endif
                @if ($event->hide_owner || $event->getOriginal('pivot_hidden'))
                <p class="event-hidden">Hidden</p>
                @endif
                <h3>{{ $event->name }}</h3>
                <p> {{ $event->description }}</p>
            </a>
            @if (Auth::check() && (Auth::user()->id == $event->id_owner || Auth::user()->id == request()->route('id') || Auth::user()->admin))
            <img src="{{ asset('icons/option.png') }}" alt="Manage Icon" class="event-manage">
            @endif
        </div>
    @endif 
@endif
