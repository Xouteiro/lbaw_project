<?php
    use App\Models\Notification;
    use Illuminate\Support\Facades\Auth;
    // check if user already sent a request to join
    if(Auth::check() && !Auth::user()->blocked){
        $hasSent = Notification::where('request_to_join.id_user', Auth::user()->id)->where('id_event', $event->id)->join('request_to_join', 'event_notification.id', '=', 'request_to_join.id_eventnotification')->first();
    }

?>


@extends('layouts.app')

@section('content')
    <div class="event-container">
        <div class="event-info"> <div class="event-page-image">
            <img src="{{ $event->getEventImage($event->id) }}" alt="Event Image" class="event-page-image">
        </div>
        @php
            $eventdate = $event->eventdate;
            $date = $eventdate[8] . $eventdate[9] . '-' . $eventdate[5] . $eventdate[6] . '-' . $eventdate[0] . $eventdate[1] . $eventdate[2] . $eventdate[3];
            $time = $eventdate[11] . $eventdate[12] . 'h' . $eventdate[14] . $eventdate[15];

            $location = $event->location->name . ', ' . $event->location->address;

            $price = $event->price == 0 ? 'Free Event' : number_format($event->price, 2, '.', '') . '€';
         @endphp

            <div class="event-header">
                <div class="event-name-container">
                    <h1 class="event-name" @if(isset($whatChanged) && isset($whatChanged->name)) style="font-weight: bold;" @endif>{{ $event->name }}</h1>
                    <div class=location-info>
                        <p @if(isset($whatChanged) && isset($whatChanged->id_location)) style="font-weight: bold;" @endif> &#128205; {{ $location}}</p>
                    </div>
                </div>
                @if (Auth::check() && (Auth::user()->id == $event->id_owner || Auth::user()->admin))
                <div class="manage-event">
                    <a title="Edit the event" class="button no-button" href="{{ route('event.edit', ['id' => $event->id]) }}">
                        &#9998;
                    </a>
                    <a title="Manage Participants" class="button no-button" href="{{ route('event.participants', ['id' => $event->id]) }}">
                        &#128101;
                    </a>
                    <div title="Delete event" class="no-button fake button delete-event" id="{{$event->id}}">
                        &#128465;
                    </div>
                </div>
                @endif
                <?php $isAdmin = (Auth::check() && Auth::user()->admin) ? 'true' : 'false' ?>
                <div class="full-date">
                    <p class="date" @if(isset($whatChanged) && isset($whatChanged->eventdate)) style="font-weight: bold;" @endif>&#128197; {{ $date }}</p> 
                    <p class="date" @if(isset($whatChanged) && isset($whatChanged->eventdate)) style="font-weight: bold;" @endif>&#128336; {{$time}} </p>
                </div>
            </div>
            <h3> About this Event </h3>
            <p @if(isset($whatChanged) && isset($whatChanged->description)) style="font-weight: bold;" @endif> {{ $event->description }}</p>
            <?php $isAdmin = (Auth::check() && Auth::user()->admin) ? 'true' : 'false' ?>
            <div id="{{$event->location->id}}" class="full-event-location" data-is-admin="{{$isAdmin}}" data-event-id="{{$event->id}}">
                <div class=location-info>
                    <p @if(isset($whatChanged) && isset($whatChanged->id_location)) style="font-weight: bold;" @endif> {{ $location}}</p>
                </div>
            </div>
            @if ($event->capacity == 0)
                @if(Auth::check() && (Auth::user()->events->contains($event) || Auth::user()->id == $event->id_owner || Auth::user()->admin))
                    <div class="participants"><a href="{{route('event.participants', ['id' => $event->id])}}">View attendees list</a><p>- {{ $event->participants->count() }} </p> </div>
                @else
                    <div class="participants"><p>Participants: {{ $event->participants->count() }} </p></div>
                @endif
            @else
                @if(Auth::check() && (Auth::user()->events->contains($event) || Auth::user()->id == $event->id_owner || Auth::user()->admin))
                    <div class="participants"><a href="{{route('event.participants', ['id' => $event->id])}}">View attendees list</a><p @if(isset($whatChanged) && isset($whatChanged->capacity)) style="font-weight: bold;" @endif>- {{ $event->participants->count() }}/{{ $event->capacity }} participants</p></div>
                @else
                    <div class="participants"><p @if(isset($whatChanged) && isset($whatChanged->capacity)) style="font-weight: bold;" @endif>Capacity: {{ $event->participants->count() }}/{{ $event->capacity }} participants</p></div>
                @endif
            @endif

            <p @if(isset($whatChanged) && isset($whatChanged->price)) style="font-weight: bold;" @endif>Price: {{ $price }} </p>
            
            @if (Auth::check())
                <div class="event-creator">
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
                    @if(isset($event->owner->id)) 
                        <img src=" {{$event->owner->getProfileImage($event->owner->id)}}" alt="Profile Image" class="profile-image">
                    @else
                        <img src="{{ url('profile/default.jpg') }}" alt="Profile Image" class="profile-image">
                    @endif
                    <p>By: 
                        @if(isset($event->owner->id)) 
                            <a href="{{ route('user.show', ['id' => $event->owner->id]) }}">
                                {{ $event->owner->name }}
                            </a>
                        @else
                            Anonymous
                        @endif
                    </p>
                </div>
            @else
                <div class="event-creator">
                    <img src="{{ url('profile/default.jpg') }}" alt="Profile Image" class="profile-image">
                    <p>By: 
                        @if(isset($event->owner->id)) 
                            <a href="{{ route('user.show', ['id' => $event->owner->id]) }}">
                                {{ $event->owner->name }}
                            </a>
                        @else
                            Anonymous
                        @endif
                    </p>
                </div>
            @endif
        </div>
        @if (isset($invite) && Auth::check() && Auth::user()->id == $invite->id_user && !Auth::user()->admin && !Auth::user()->blocked)
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
            @if (Auth::check() && Auth::user()->id == $event->id_owner && !Auth::user()->admin)
                <form action="{{ route('event.leave', ['id' => $event->id]) }}" method="POST">
                    @csrf
                    <button class="button" type="submit">
                        Leave Event
                    </button>
                </form>
            @endif
            @if (Auth::check() && Auth::user()->id == $event->id_owner && !Auth::user()->admin && !Auth::user()->blocked)
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
                    <form method="POST" action="{{ route('invite.send') }}" id="invitationForm">
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
                <h5>No comments yet</h4>
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
        @if (Auth::check() && !Auth::user()->blocked && (Auth::user()->events->contains($event) || Auth::user()->id == $event->id_owner))
            <div class="general add-comment">
                <input type="hidden" name="id_user" value="{{ Auth::user()->id }}">
                <input type="hidden" name="id_event" value="{{ $event->id }}">
                <label for="comment">Comment</label>
                <textarea id="comment" name="comment" rows="4" cols="50" required></textarea>
                <button class="add-comment button" type="button">Comment</button>
            </div>
        @endif
        @if (Auth::check() && !Auth::user()->blocked && (Auth::user()->events->contains($event) || Auth::user()->id == $event->id_owner))
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
