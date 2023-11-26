@extends('layouts.app')

@section('content')
    <div class="container home-page">
        <div class= "background-image">
            <img src="{{ url('images/homepage.png') }}" alt="Home page image">
            <div class="overlay-text">
                <h1> Welcome to Invents </h1>
                <h2>Dive into a world of exciting events </h2>
                <h3>Each one a special adventure waiting for you</h3>
            </div>
        </div>
         
        <form id="searchForm" action="{{ route('events.search') }}" method="GET">
            <input name="search" value="" placeholder="Search event" class="search-event"/>
            <button type="submit" id="searchButton">Search</button>
            <a class = 'button' href="{{ route('events') }}">Check them all!</a>
        </form> 
    <!--
        <div class = 'home-page-image-container'>
            <img class = 'home-page-image' src="{{ url('images/concerto.jpg') }}" alt="Concerto">
            <img class = 'home-page-image' src="{{ url('images/estadio.jpg') }}" alt="Estadio">
            <img class = 'home-page-image' src="{{ url('images/opera.jpg') }}" alt="Opera">
        </div>
    -->
        <div class="events-container">
            @each('partials.event_card', $events, 'event')
        </div>
    </div>
@endsection
