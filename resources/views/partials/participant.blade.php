<div class="participant-card" id="{{$participant->id}}">
    <img class="user" src="{{ $participant->getProfileImage() }}">
    <div>
        <h2><a href="{{ route('user.show', ['id' => $participant->id]) }}">{{ $participant->name }}</a></h2>
        <h3><a href="{{ route('user.show', ['id' => $participant->id]) }}">{{ $participant->username }}</a></h3>
        <p>Joined: {{ $participant->getOriginal('pivot_date') }}</p>
    </div>
    @if(Auth::check() && Auth::user()->blocked && (Auth::user()->id == $event->id_owner || Auth::user()->admin))
        <button type="button" class="fake button remove" id="{{$event->id}}">
            Remove Participant
        </button>
    @endif
</div>
