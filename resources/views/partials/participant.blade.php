<div class="participant-card" id="{{$participant->id}}">
    @if($event->id_owner == $participant->id)
    <h2>{{ $participant->name }} - Owner</h2>
    @else
    <h2>{{ $participant->name }} </h2>
    @endif
    <h3>{{ $participant->username }}</h3>
    <p>{{ $participant->description }}</p>
    <a href="{{ route('user.show', ['id' => $participant->id]) }}">
        <button class="button" type="button">
            View Profile
        </button>
    </a>
    <a>
        <button class="button" type="button">
            Send Message
        </button>
    </a>
    <form action="{{ route('event.removeparticipant', ['id' => $event->id, 'id_p' => $participant->id]) }}" method="POST">
        @csrf
        <div class="fake button remove" id="{{$participant->id}}">
            Remove Participant
        </div>
    </form>
</div>
