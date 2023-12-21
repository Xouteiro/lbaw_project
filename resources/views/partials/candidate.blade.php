<div class="participant-card" id="{{$user->id}}">
    <img class="user" src="{{ $user->getProfileImage() }}">
    <div>
        <h2><a href="{{ route('user.show', ['id' => $user->id]) }}">{{ $user->name }}</a></h2>
        <h3><a href="{{ route('user.show', ['id' => $user->id]) }}">{{ $user->username }}</a></h3>
    </div>
    @if(Auth::check() && (Auth::user()->id == $event->id_owner || Auth::user()->admin))
        <button type="button" class="fake button accept" id="{{$event->id}}">
            Accept as new Admin
        </button>
    @endif
</div>
