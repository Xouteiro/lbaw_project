<?php
    use App\Models\Notification;
    use Illuminate\Support\Facades\Auth;
    // check if user already sent a request to join
    if(Auth::check()){
        $hasSent = Notification::where('request_to_join.id_user', Auth::user()->id)->where('id_event', $event->id)->join('request_to_join', 'event_notification.id', '=', 'request_to_join.id_eventnotification')->first();
    }

?>

@extends('layouts.app')

@section('content')
    <div class="event-container">
        <div class="event-info">
            <div class="event-header">
                <h1 class="event-name" @if(isset($whatChanged) && isset($whatChanged->name)) style="font-weight: bold;" @endif>{{ $event->name }}</h1>
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
            <p @if(isset($whatChanged) && isset($whatChanged->description)) style="font-weight: bold;" @endif>Description: {{ $event->description }}</p>
            <p @if(isset($whatChanged) && isset($whatChanged->eventdate)) style="font-weight: bold;" @endif>Event date: {{ $event->eventdate }}</p>
            @if ($event->capacity == 0)
                @if(Auth::check() && (Auth::user()->events->contains($event) || Auth::user()->id == $event->id_owner || Auth::user()->admin))
                    <div class="participants"><p>Participants: {{ $event->participants->count() }} </p> <a href="{{route('event.participants', ['id' => $event->id])}}">View attendees list</a></div>
                @else
                    <div class="participants"><p>Participants: {{ $event->participants->count() }} </p></div>
                @endif
            @else
                @if(Auth::check() && (Auth::user()->events->contains($event) || Auth::user()->id == $event->id_owner || Auth::user()->admin))
                    <div class="participants"><p @if(isset($whatChanged) && isset($whatChanged->capacity)) style="font-weight: bold;" @endif>Capacity: {{ $event->participants->count() }}/{{ $event->capacity }}</p><a href="{{route('event.participants', ['id' => $event->id])}}">View attendees list</a></div>
                @else
                    <div class="participants"><p @if(isset($whatChanged) && isset($whatChanged->capacity)) style="font-weight: bold;" @endif>Capacity: {{ $event->participants->count() }}/{{ $event->capacity }}</p></div>
                @endif
            @endif
            @if ($event->price == 0)
                <p @if(isset($whatChanged) && isset($whatChanged->price)) style="font-weight: bold;" @endif>Free Event</p>
            @else
                <p @if(isset($whatChanged) && isset($whatChanged->price)) style="font-weight: bold;" @endif>Price: {{ $event->price }} €</p>
            @endif
            <?php $isAdmin = (Auth::check() && Auth::user()->admin) ? 'true' : 'false' ?>
            <div id="{{$event->location->id}}" class="full-event-location" data-is-admin="{{$isAdmin}}" data-event-id="{{$event->id}}">
                <div class=location-info>
                    <p @if(isset($whatChanged) && isset($whatChanged->id_location)) style="font-weight: bold;" @endif>Location: {{ $event->location->name }}</p>
                    <p @if(isset($whatChanged) && isset($whatChanged->id_location)) style="font-weight: bold;" @endif>Address: {{ $event->location->address }}</p>
                </div>
            </div>
        </div>
        @if (isset($invite) && Auth::check() && Auth::user()->id == $invite->id_user && !Auth::user()->admin)
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
        @if($event->eventdate > date('Y-m-d H:i:s'))
            @if (
                $event->opentojoin &&
                    Auth::check() &&
                    Auth::user()->id != $event->id_owner &&
                    !Auth::user()->events->contains($event) &&
                    !Auth::user()->admin
                    )
                <form action="{{ route('event.join', ['id' => $event->id]) }}" method="POST">
                    @csrf
                    <button class="button" type="submit">
                        Join Event
                    </button>
                </form>
            @elseif(!$event->opentojoin && Auth::check() &&
                    Auth::user()->id != $event->id_owner &&
                    !Auth::user()->events->contains($event) &&
                    !Auth::user()->admin
                    )
                <button class="fake button @if($hasSent)sent @endif" type="button"
                    id="{{ $event->id }}" onclick="requestToJoin(this)">
                    @if($hasSent)
                    Request sent
                    @else
                    Request to join
                    @endif
                </button>
            @elseif(Auth::check() && Auth::user()->id != $event->id_owner && Auth::user()->events->contains($event) && !Auth::user()->admin)
            
                <form action="{{ route('event.leave', ['id' => $event->id]) }}" method="POST">
                    @csrf
                    <button class="button" type="submit">
                        Leave Event
                    </button>
                </form>
            @endif
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
        @endif
        <div class="comments">
            <h3>Comments</h3>
            @if($event->comments->count() == 0)
                <p>No comments yet</p>
            @else
            @php
                $ownerComments = $event->comments->filter(function ($comment) use ($event) {
                    return $comment->id_user == $event->id_owner;
                })->sortByDesc('date');
            
                $otherComments = $event->comments->filter(function ($comment) use ($event) {
                    return $comment->id_user != $event->id_owner;
                })->sortByDesc('date');
            @endphp
                <ul class="comment-list">
                    @foreach($ownerComments as $comment)
                         @include('partials.comment', ['comment' => $comment, 'event' => $event])
                    @endforeach 
                    @foreach($otherComments as $comment)
                         @include('partials.comment', ['comment' => $comment, 'event' => $event])
                    @endforeach
                </ul>
        @endif
        </div>
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
                <div>
                <h3 class="event_id_holder" id="{{$event->id}}">Polls</h3>
                @if (Auth::check() && Auth::user()->id == $event->id_owner)
                    <button class="fake-poll-create-button">
                        Create Poll
                    </button>
                @endif
                </div>
                    <ul class="poll-list">
                    @if($event->polls->count() == 0)
                        <p class="no-polls">No polls yet</p>
                    @else
                        @each('partials.poll', $event->polls->reverse(), 'poll')
                    @endif
                    </ul>
            </div>
        @endif
    </div>
@endsection
