@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>{{ $event->name }}</h1>
        @if (Auth::check() && Auth::user()->id == $event->id_user)
            <a href="{{ url('/event' . $event->id) . '/edit' }}">Edit Event</a>
        @endif
        <form id="invitationForm">
            @csrf
            <input type="text" name="email" placeholder="Enter user's email">
            <input type="hidden" name="eventId" value="{{ $event->id }}">
            <button type="button" onclick="sendInvitation()">Send Invitation</button>
        </form>
        <p>Owner id: {{ $event->id_user }}</p>
        <p>Event date: {{ $event->eventdate }}</p>
        <p>Price: {{ $event->price }}</p>
        <p>Description: {{ $event->description }}</p>
        <p>Location id:{{ $event->id_location }}</p>
        @if ($event->public)
            <p>PUBLIC</p>
            <form action="" method="POST">
                <button class="join_button" type="submit">
                    <p> Joint Event</p>
                </button>
            </form>
        @else
            <p>PRIVATE</p>
        @endif
        @if ($event->opentojoin)
            <p>Open to join</p>
        @else
            <p>Only Invited people can join</p>
        @endif
        <div class="tags">
            @foreach ($event->tags() as $tag)
                <p>{{ $tag->name }}</p>
            @endforeach
        </div>
        <div class="polls">
            <ul>
                @each('partials.poll', $event->polls(), 'poll')
            </ul>
        </div>
        <div class="comments">
            <ul>
                @foreach ($event->comments() as $comment)
                    <li>
                        <h3>{{ $comment->user()->username }}</h3>
                        @if (Auth::check() && Auth::id() === $comment->user()->id)
                            <form action="" method="POST">
                                <button class="delete_comment_button" type="submit">
                                    <p> Delete comment</p>
                                </button>
                            </form>
                            <form action="" method="POST">
                                <button class="edit_comment_button" type="submit">
                                    <p> Edit comment</p>
                                </button>
                            </form>
                        @endif
                        <p>{{ $comment->text }}</p>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
