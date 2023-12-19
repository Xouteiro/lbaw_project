@extends('layouts.app')

@section('content')
    <div class="container mainFeatures-page">
        <div class= "background-image">
            <img src="{{ url('images/homepage.png') }}" alt="Home page image">
            <div class="overlay-text">
                <h1 style="white-space:nowrap">Invents's Main Features</h1>
                <h2> Here is why you should use Invents </h2>
                <h4> Checkout the Main Features of our website: </h4>
            </div>
        </div>
        <div class="main">
            <a href="{{ route('events') }}">
            <div class="about-card">
                <h2>Search Events!</h3>
                <p>Dedicated to offering the best user experience, Invents provides Full text and exact match search for all events.</p>
            </div>
            </a> 
            @if(Auth::check())
            <a href= "{{ url('/user/' . Auth::user()->id) }}"">
            @else
            <a href="{{ route('login') }}">
            @endif
            <div class="about-card">
                <h2>Hide and Pin Events</h3>
                <p>Powered by cutting-edge technology, Invents let you Pin and Hide Events on your profile in a fast and responsive way using AJAX</p>
            </div>
            </a>
            @if(Auth::check())
            <a href= "{{ url('/user/' . Auth::user()->id) }}"">
            @else
            <a href="{{ route('login') }}">
            @endif
            <div class="about-card">
                <h2>Manage Event Participants</h3>
                <p>User-friendly and intuitive. Invents simplifies the management of the Events and their participants</p>
            </div>
            </a>
        </div>
        <div class="main">
            @if(Auth::check())
            <a href= "{{ url('/user/' . Auth::user()->id) }}"">
            @else
            <a href="{{ route('login') }}">
            @endif
            <div class="about-card">
                <h2>Effortless Event Creation </h2>
                <p>Invents empowers you to effortlessly create, manage, all from a single intuitive
                    platform. Invite participants, share event details, and gather feedback, ensuring a smooth and engaging experience for all.</p>
            </div>
            </a>
            <a href="{{ route('events') }}">
            <div class="about-card">
                <h2>Tailored Event Experiences</h2>
                <p> Invents helps you discover events that align with your interests and preferences.  
                    Browse through a diverse range of events, filter by location, and stay updated with real-time notifications.</p>
            </div>
            </a>
            <a href="{{ route('events') }}">
            <div class="about-card">
                <h2>Engaging Event Interactions </h2>
                <p>Immerse yourself in the event experience by engaging in polls. Share your
                    thoughts, ask questions, and connect with others.
                </p>
            </div>
            </a>
        </div>
    </div>
@endsection
