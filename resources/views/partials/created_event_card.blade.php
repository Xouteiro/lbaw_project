@if ($event->public || Auth::check())
    <div id="event-{{ $event->id }}" class="event-card">
        <a href="{{ route('event.show', ['id' => $event->id]) }}">
            @if ($event->highlight_owner)
            <img src="{{ asset('icons/pin.png') }}" alt="Pin Icon" class="event-pin">
            @endif
            @if ($event->hide_owner)
            <p class="event-hidden">Hidden</p>
            @endif
            @php
                $description = $event->description;
                if (strlen($description) > 70){
                    $description = substr($description, 0, 67) . '...';
                }
            @endphp
            <img src="{{ $event->getEventImage($event->id) }}" alt="Event Image" class="event-image">
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

