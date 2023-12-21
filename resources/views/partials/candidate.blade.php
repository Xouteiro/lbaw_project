<h4>{{$user->name}}</h4>
<div class="participant-card" id="{{$user->id}}">
    <img class="user" src="{{ $user->getProfileImage() }}">
    <div>
        <h2><a href="{{ route('user.show', ['id' => $user->id]) }}">{{ $user->name }}</a></h2>
        <h3><a href="{{ route('user.show', ['id' => $user->id]) }}">{{ $user->username }}</a></h3>
    </div>
    <button type="button" class="fake button accept" id="{{$user->id}}">
        Answer request
    </button>
</div>
