<div class="participant-card" id="{{$participant->id}}">
    <img class="user" src="{{ $participant->getProfileImage() }}">
    <div>
        <h2><a href="{{ route('user.show', ['id' => $participant->id]) }}">{{ $participant->name }}</a></h2>
        <h3><a href="{{ route('user.show', ['id' => $participant->id]) }}">{{ $participant->username }}</a></h3>
        <p>Joined: {{ $participant->getOriginal('pivot_date') }}</p>
    </div>
    @if(Auth::check() && (Auth::user()->id == $event->id_owner || Auth::user()->admin))
    <form action="{{ route('event.removeparticipant', ['id' => $event->id, 'id_p' => $participant->id]) }}" method="POST">
        @csrf
        <div class="fake button remove" id="{{$participant->id}}">
            Remove Participant
        </div>
    </form>
    @endif
</div>
