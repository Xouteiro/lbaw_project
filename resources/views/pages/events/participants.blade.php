@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>{{$event->name}} Participants</h1>
        <div class="participants">
            @if($event->id_owner !== null)
                <div class="participant-card" id="owner">
                    <img class='user' src="{{ $event->owner->getProfileImage() }}">
                    <div class="participant-info">
                        <h2><a href="{{ route('user.show', ['id' => $event->owner->id]) }}">{{ $event->owner->name }} - Owner</a></h2>
                        <h3><a href="{{ route('user.show', ['id' => $event->owner->id]) }}">{{ $event->owner->username }}</a></h3>
                    </div>
                </div>
            @else
                <div class="participant-card" id="owner">
                    <img class='user' src="{{ url('profile/default.jpg') }}">
                    <div class="participant-info">
                        <h2>Anonymous - Owner</h2>
                        <h3>Anonymous</h3>
                    </div>
                </div>
            @endif
            @if($event->participants->count() == 0)
                <h4>No participants yet</h4>
            @else
            @foreach($event->participants as $participant)
                @include('partials.participant', ['participant' => $participant])
            @endforeach
            @endif
        </div>
        @if (Auth::check() && Auth::user()->id == $event->id_owner && !Auth::user()->admin)
                <div class="invite-container">
                    <h3>Invite a user to this event</h3>
                    
                    @if(session('success'))
                        <span class="success">
                            {{ session('success') }}
                        </span>
                    @endif
                    @if ($errors->has('invite'))
                        <span class="error">
                            {{ $errors->first('invite') }}
                        </span>
                    @endif
                    <form method="POST" action="{{ route('invite.send') }}" id="invitationForm" style="margin: 0;">
                        @csrf
                        <input type="text" name="username" placeholder="Enter user's username">
                        <input type="hidden" name="id_event" value="{{ $event->id }}">
                        <button type="submit">
                            Send Invitation
                        </button>
                    </form>
                </div>
            @endif
    </div>
@endsection
