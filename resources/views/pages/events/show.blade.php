@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>{{ $event->name }}</h1>
        <p>Event Creator: <a href="{{ url('/user/' . $event->owner->id) }}"> {{ $event->owner->name }}</a></p>
        <p>Event date: {{ $event->eventdate }}</p>
        @if ($event->price == 0)
            <p>Free Event</p>
        @else
        <p>Price: {{ $event->price }} €</p>
        @endif
        <p>Description: {{ $event->description }}</p>
        <p>Location: {{ $event->location->address }}</p>
        @if ($event->opentojoin)
            <form action="" method="POST">
                <button class="button" type="submit">
                    Join Event
                </button>
            </form>
        @else
            <form action="" method="POST">
                <button class="button" type="submit">
                    Request to join
                </button>
            </form>
        @endif
        @if (Auth::check() && Auth::user()->id == $event->id_owner)
            <a class="button" href="{{ route('event.edit', ['id' => $event->id]) }}">
                Edit Event
            </a>
        @endif
        
        <div class="comments">
            <ul>
                @foreach ($event->comments as $comment)
                    <li>
                        <h3>{{ $comment->user->username }}</h3>
                        @if (Auth::check() && Auth::id() === $comment->user->id)
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
@endsection
