@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Admin Candidates</h1>
        <div class="participants">
            @if ($users->count() == 0)
                <h4>No candidates yet</h4>
            @else
                @foreach ($users as $user)
                    <div class="participant-card" id="{{ $user->id }}">
                        <img class="user" src="{{ $user->getProfileImage() }}">
                        <div>
                            <h2><a href="{{ route('user.show', ['id' => $user->id]) }}">{{ $user->name }}</a></h2>
                            <h3><a href="{{ route('user.show', ['id' => $user->id]) }}">{{ $user->username }}</a></h3>
                        </div>
                        <button type="button" class="fake button accept" id="{{ $user->id }}">
                            Answer request
                        </button>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
@endsection
