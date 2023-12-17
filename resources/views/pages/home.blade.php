@extends('layouts.app')

@section('content')
    <div class="container home-page">
        <div class= "background-image">
            <img src="{{ url('images/homepage.png') }}" alt="Home page image">
            <div class="overlay-text">
                <h1>Welcome to Invents</h1>
                <h2>Dive into a world of exciting events </h2>
                <h3>Each one a special adventure waiting for you</h3>
                <h4>Here are some handpicked events for you.</h4>
            </div>
        </div>
        <div class="events-container">
            @each('partials.event_card', $events, 'event')
        </div>
        <div class="check-all">
            <h3>Still curious?</h3>
            <a class = 'button' href="{{ route('events') }}">Check them all!</a>
        </div>
    </div>
@endsection
