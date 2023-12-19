@extends('layouts.app')

@section('content')
    <div class="container about-page">
        <div class= "background-image">
            <img src="{{ url('images/homepage.png') }}" alt="Home page image">
            <div class="overlay-text">
                <h1>About Invents</h1>
                <h2>Embrace the Event Experience with Invents </h2>
                <h4>Invents is your gateway to a world of captivating events, seamlessly connecting event organizers with
                    enthusiastic attendees.</h4>
            </div>
        </div>
        <div class="call-to-action">
            <h2>Discover Exciting Events &#127757;</h2>
            <h4>Whether you're an event organizer seeking seamless management or an attendee eager to discover new
                experiences, Invents is your perfect companion.
            </h4>
            @if(Auth::check())
                <h4><a href="{{ route('events') }}" class="btn btn-primary">Embark on an Enriching Event Journey</a></h4>
            @else
                <h4><a href="{{ route('login') }}" class="btn btn-primary">Embark on an Enriching Event Journey</a></h4>
            @endif
        </div>
        <div class="contact-us">
            <h2>Contact Us &#9742;</h2>
            <h4>Get in Touch! We are a group of students always happy to answer your questions and provide assistance. Please feel free to
                contact us using
                the following methods:</h4>
            <ul class="names">
                <li class="name-card">
                    <p><strong>Name:</strong> Guilherme Coutinho</p>
                    <p><strong>Email:</strong> <a href="mailto:up202108872@fe.up.pt">up202108872@fe.up.pt</a></p>
                </li>
                <li class="name-card">
                    <p><strong>Name:</strong> José Luiz Caribé</p>
                    <p><strong>Email:</strong> <a href="mailto:up202103344@fe.up.pt">up202103344@fe.up.pt</a></p>
                </li>
                <li class="name-card">
                    <p><strong>Name:</strong> Xavier Outeiro</p>
                    <p><strong>Email:</strong> <a href="mailto:up202108895@fe.up.pt">up202108895@fe.up.pt</a></p>
                </li>
            </ul>
        </div>
    </div>
@endsection
