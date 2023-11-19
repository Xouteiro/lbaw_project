@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>{{ $user->username }}</h1>
        @if (Auth::check() && Auth::user()->id == $user->id)
            <a href="{{ url('/user/' . Auth::user()->id) .'/edit'}}">Edit Profile</a>
        @endif
        <p>{{ $user->description }}</p>
    </div>
@endsection
