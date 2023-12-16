@extends('layouts.app')

@section('content')
    <div class="container profile">
        <div class="profile-header-container">
            <div class="profile-header">
                <img src="{{ $user->getProfileImage() }}">
            </div>
            <div class='profile-header'>
                <h1>{{ $user->username }}</h1>
                <p>{{ $user->description }}</p>
            </div>
        </div>
        @if (Auth::check() && (Auth::user()->id === $user->id || Auth::user()->admin))
            <div class="account-owner admin">
                <a class="button" href="{{ route('event.create') }}">Create Event</a>
                <a class="button" href="{{ url('/user/' . $user->id .'/edit')}}">Edit Profile</a>
                <div class="fake button delete-account" id="{{$user->id}}">
                    Delete Account
                </div>
            </div>
        @endif
        @if (AutH::check() && Auth::user()->id === $user->id)
            <div class="account-owner">
                <a class="button" href="{{ url('/logout') }}"> Logout </a>
            </div>
        @endif

        <div class="profile-events-title-div">
            <h2 class="joined-events-title active">Joined Events</h2>
            <h2 class="created-events-title">Created Events</h2>
        </div>
        <div class="joined-events-container" style="display: flex">
            @each('partials.joined_event_card', $user->events, 'event')
        </div>
        <div class="created-events-container" style="display: none">
            @each('partials.created_event_card', $user->ownedEvents, 'event')
        </div>
    </div>
@endsection
