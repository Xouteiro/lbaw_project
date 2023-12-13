<div class="participant-card" id="{{$participant->id}}">
    <div>
        <h2><a href="{{ route('user.show', ['id' => $participant->id]) }}">{{ $participant->name }}</a></h2>
        <h3><a href="{{ route('user.show', ['id' => $participant->id]) }}">{{ $participant->username }}</a></h3>
    </div>
    <form action="{{ route('event.removeparticipant', ['id' => $event->id, 'id_p' => $participant->id]) }}" method="POST">
        @csrf
        <div class="fake button remove" id="{{$participant->id}}">
            Remove Participant
        </div>
    </form>
</div>
