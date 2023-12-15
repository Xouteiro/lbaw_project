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
                    <div class="fake button delete-event" id="{{$event->id}}">
                        Delete Event
                    </div>
                @endif
            </div>
            @if (Auth::check())
                <p>Event Creator: 
                    @if(isset($event->owner->id)) 
                        <a href="{{ route('user.show', ['id' => $event->owner->id]) }}">
                            {{ $event->owner->name }}
                        </a>
                    @else
                        Anonymous
                    @endif
                </p>
            @else
                <p>Event Creator: 
                    @if(isset($event->owner->id)) 
                        <a href="{{ route('login') }}"> 
                            {{ $event->owner->name }}
                        </a>
                    @else
                        Anonymous
                    @endif
                </p>
            @endif
            <p>Description: {{ $event->description }}</p>
            <p>Event date: {{ $event->eventdate }}</p>
            @if ($event->capacity == 0)
                @if(Auth::check() && (Auth::user()->events->contains($event) || Auth::user()->id == $event->id_owner))
                    <div class="participants"><p>Participants: {{ $event->participants->count() }} </p> <a href="{{route('event.participants', ['id' => $event->id])}}">View attendees list</a></div>
                @else
                <div class="participants"><p>Participants: {{ $event->participants->count() }} </p></div>
                @endif 
            @else
                @if(Auth::check() && (Auth::user()->events->contains($event) || Auth::user()->id == $event->id_owner))
                    <div class="participants"><p>Capacity: {{ $event->participants->count() }}/{{ $event->capacity }}</p><a href="{{route('event.participants', ['id' => $event->id])}}">View attendees list</a></div>
                @else
                <div class="participants"><p>Capacity: {{ $event->participants->count() }}/{{ $event->capacity }}</p></div>
                @endif
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
        @elseif(!$event->opentojoin && Auth::check() &&
                Auth::user()->id != $event->id_owner &&
                !Auth::user()->events->contains($event))

            @if(session('success'))
                <span class="success">
                    {{ session('success') }}
                </span>
            @endif
            @if ($errors->has('requestToJoin'))
                <span class="error">
                    {{ $errors->first('requestToJoin') }}
                </span>
            @endif
            <form action="{{ route('requestToJoin.send') }}" method="POST">
                @csrf
                <input type="hidden" name="id_event" value="{{ $event->id }}">
                <button class="button" type="submit">
                    Request to join
                </button>
            </form>
        @endif
        @if (Auth::check() && Auth::user()->id == $event->id_owner)
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
                    <input type="text" name="email" placeholder="Enter user's email">
                    <input type="hidden" name="id_event" value="{{ $event->id }}">
                    <button type="submit">
                        Send Invitation
                    </button>
                </form>
            </div>
        @endif
        @if(Auth::check())
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
        @endif
        @if ((Auth::check() && Auth::user()->events->contains($event)) || Auth::check() && Auth::user()->id == $event->id_owner)
            <div>
                <form class="general" action="{{ route('comment.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id_user" value="{{ Auth::user()->id }}">
                    <input type="hidden" name="id_event" value="{{ $event->id }}">
                    <label for="comment">Comment</label>
                    @if ($errors->has('comment'))
                        <span class="error">
                            {{ $errors->first('comment') }}
                        </span>
                    @endif
                    <textarea id="comment" name="comment" rows="4" cols="50" required></textarea>
                    <button type="submit">Comment</button>
                </form>
            </div>
        @endif
        @if ((Auth::check() && Auth::user()->events->contains($event)) || Auth::check() && Auth::user()->id == $event->id_owner)
            <div class="polls">
                <h3>Polls</h3>
                @if($event->polls->count() == 0)
                    <p>No polls yet</p>
                @else
                    <ul class="poll-list">
                        @each('partials.poll', $event->polls, 'poll')
                    </ul>
                @endif
            </div>
        @endif
    </div>
@endsection
