<?php if($event->public) { ?>
    <div class="event-card">
        <a href="{{ route('event.show', ['id' => $event->id]) }}">
            <h3>{{ $event->name }}</h3>
            <p> {{ $event->description }}</p>
        </a>
    </div>
<?php } ?>
