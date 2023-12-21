@if ($event->public || (Auth::check() && !Auth::user()->blocked))
    <div id="event-{{ $event->id }}" class="event-card">
        <a href="{{ route('event.show', ['id' => $event->id]) }}">
            @if ($event->getOriginal('pivot_highlighted'))
                <img src="{{ asset('icons/pin.png') }}" alt="Pin Icon" class="event-pin">
            @endif
            @if ($event->getOriginal('pivot_hidden'))
                <p class="event-hidden">Hidden</p>
            @endif
            
            @if($event->eventdate < date('Y-m-d'))
                <p class="status" id="Finished" >Finished</p>
                @elseif($event->eventdate > date('Y-m-d'))
                    <p class="status" id="Upcoming">Upcoming</p>
                @endif
            <img src="{{ $event->getEventImage($event->id) }}" alt="Event Image" class="event-image">
            @php
                $description = $event->description;
                if (strlen($description) > 70){
                    $description = substr($description, 0, 67) . '...';
                }
                $eventdate = $event->eventdate;
                $date = $eventdate[8] . $eventdate[9] . '/' . $eventdate[5] . $eventdate[6] . '/' . $eventdate[0] . $eventdate[1] . $eventdate[2] . $eventdate[3];
                $time = $eventdate[11] . $eventdate[12] . 'h' . $eventdate[14] . $eventdate[15];
            @endphp
            <div class="event-info">
                <h3>{{ $event->name }}</h3>
                <p>{{ $description }}</p>
                @if($event->eventdate < date('Y-m-d'))
                    <p class="Finished"> &#128197; {{ $date }} &#128336; {{$time}} </p>
                @else
                    <p> &#128197; {{ $date }} &#128336; {{$time}} </p>
                @endif
            </div>
        </a>
        @if (Auth::check() && !Auth::user()->blocked && (Auth::user()->id == $event->id_owner || Auth::user()->id == request()->route('id') || Auth::user()->admin))
            <img src="{{ asset('icons/option.png') }}" alt="Manage Icon" class="event-manage">
        @endif
    </div>
@endif