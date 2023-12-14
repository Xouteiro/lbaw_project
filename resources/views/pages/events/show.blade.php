@extends('layouts.app')

@section('content')
    <div class="event-container">
        <div class="event-info">
            <div class="event-header">
                <h1 class="event-name">{{ $event->name }}</h1>
                @if (Auth::check() && (Auth::user()->id == $event->id_owner || Auth::user()->admin))
                    <a class="button" href="{{ route('event.edit', ['id' => $event->id]) }}">
                        Edit Event
                    </a>
                    <a class="button" href="{{ route('event.participants', ['id' => $event->id]) }}">
                        Manage Participants
                    </a>
                    <form action= "{{ route('event.delete', ['id' => $event->id]) }}" method="POST">
                        @csrf
                        <button class="button" type="submit">
                            Delete Event
                        </button>
                    </form>
                @endif
            </div>
            @if (Auth::check())
                <p>Event Creator: <a href="{{ route('user.show', ['id' => $event->owner->id]) }}">
                        {{ $event->owner->name }}</a></p>
            @else
                <p>Event Creator: <a href="{{ route('login') }}"> {{ $event->owner->name }}</a></p>
            @endif
            <p>Description: {{ $event->description }}</p>
            <p>Event date: {{ $event->eventdate }}</p>
            @if ($event->capacity == 0)
                <p>Participants: {{ $event->participants->count() }} </p>
            @else
                <p>Capacity: {{ $event->participants->count() }}/{{ $event->capacity }}</p>
            @endif
            @if ($event->price == 0)
                <p>Free Event</p>
            @else
                <p>Price: {{ $event->price }} €</p>
            @endif
            <p>Location: {{ $event->location->name }}</p>
            <p>Address: {{ $event->location->address }}</p>

        </div>
        @if (isset($invite) && Auth::check() && Auth::user()->id == $invite->id_user)
            {{-- Form of invite decision (Accept/Deny) --}}
            <div class="invite-decision">
                <h3>You have been invited for this event!</h3>
                <form action="{{ route('invite.accept') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id_invite" value="{{ $invite->id }}">
                    <button class="button" name="accept" value="accept" type="submit">
                        Accept
                    </button>
                    <button class="button" name="deny" value="deny" type="submit">
                        Deny
                    </button>
                </form>
            </div>
        @endif
        @if (
            $event->opentojoin &&
                Auth::check() &&
                Auth::user()->id != $event->id_owner &&
                !Auth::user()->events->contains($event))
            <form action="{{ route('event.join', ['id' => $event->id]) }}" method="POST">
                @csrf
                <button class="button" type="submit">
                    Join Event
                </button>
            </form>
        @elseif(
            !$event->opentojoin &&
                Auth::check() &&
                Auth::user()->id != $event->id_owner &&
                !Auth::user()->events->contains($event))
            <form action="" method="POST">
                @csrf
                <button class="button" type="submit">
                    Request to join
                </button>
            </form>
        @elseif(Auth::check() && Auth::user()->id != $event->id_owner && Auth::user()->events->contains($event))
            <form action="{{ route('event.leave', ['id' => $event->id]) }}" method="POST">
                @csrf
                <button class="button" type="submit">
                    Leave Event
                </button>
            </form>
        @endif
        @if (Auth::check() && Auth::user()->id == $event->id_owner)
            <div class="invite-container">
            <h3>Invite a user to this event</h3>
            <form method="POST" action="{{ route('invite.send') }}" id="invitationForm" style="margin: 0;">
                @csrf
                <input type="text" name="email" placeholder="Enter user's email">
                <input type="hidden" name="id_event" value="{{ $event->id }}">
                <button type="submit">Send Invitation</button>
            </form>
            @if(session()->has('message'))
            <div class="alert alert-success">
            {{ session()->get('message') }}
            </div>
            @endif
            @if ($errors->has('invite'))
            <span class="error" style="margin: 1em 0; color: red;">
            {{ $errors->first('invite') }}
            </span>
            @endif
            </div>
        @endif
        <div class="comments">
            <h3>Comments</h3>
            @if($event->comments->count() == 0)
            <p>No comments yet</p>
            @else
            <ul class="comment-list">
                @each('partials.comment', $event->comments, 'comment')
            </ul>
            @endif
        </div>
    </div>
@endsection
