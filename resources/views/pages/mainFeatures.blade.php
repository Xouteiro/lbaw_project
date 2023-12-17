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
            <div class="feature">
                <h3>Search Events!</h3>
                <p>Dedicated to offering the best user experience, Invents provides Full text and exact match search for all events.</p>
            </div>

            <div class="feature">
                <h3>Hide and Pin Events on your profile page</h3>
                <p>Powered by cutting-edge technology, Invents let you Pin and Hide Events on your profile in a fast and responsive way using AJAX</p>
            </div>

            <div class="feature">
                <h3>Manage Event Participants</h3>
                <p>User-friendly and intuitive, Invents simplifies the management of the Event making it possible for the Event Owner to view participants's profile page and remove them from the event</p>
            </div>
        </div>
    </div>
@endsection
