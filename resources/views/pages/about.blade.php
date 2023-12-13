@extends('layouts.app')

@section('content')
    <div class="container about-page">
        <div class= "background-image">
            <img src="{{ url('images/homepage.png') }}" alt="Home page image">
            <div class="overlay-text">
                <h1> About Invents </h1>
                <h2>Embrace the Event Experience with Invents </h2>
                <h4>Invents is your gateway to a world of captivating events, seamlessly connecting event organizers with
                    enthusiastic attendees.</h4>
            </div>
        </div>

        <div class="main">
            <div class="about-card">
                <h2>Effortless Event Creation for Organizers</h2>
                <p>Invents empowers you to effortlessly create, manage, and promote your events, all from a single intuitive
                    platform. Seamlessly invite participants, share event details, and gather feedback, ensuring a smooth
                    and
                    engaging experience for all.</p>
            </div>
            <div class="about-card">
                <h2>Tailored Event Experiences for Attendees</h2>
                <p>Whether you're seeking knowledge, entertainment, or social connections, Invents helps you discover events
                    that
                    align with your interests and preferences. Browse through a diverse range of events, filter by location,
                    and
                    stay updated with real-time notifications.</p>
            </div>
            <div class="about-card">
                <h2>Engaging Event Interactions and Feedback</h2>
                <p>Immerse yourself in the event experience by engaging in polls and discussions. Share your
                    thoughts, ask questions, and connect with other attendees, ensuring a lively and interactive atmosphere.
                </p>
            </div>
        </div>

        <div class="call-to-action">
            <h2>Discover Exciting Events</h2>
            <h4>Whether you're an event organizer seeking seamless management or an attendee eager to discover new
                experiences, Invents is your perfect companion.
                <a href="{{ route('register') }}" class="btn btn-primary">Embark on an Enriching Event Journey</a>
            </h4>
        </div>

        <div class="contact-us">
            <h2>Contact Us</h2>
            <p>Get in Touch! We are a group of students always happy to answer your questions and provide assistance. Please feel free to
                contact us using
                the following methods:</p>

            <ul>
                <li>
                    <p><strong>Name</strong>Guilherme Coutinho</p>
                    <p><strong>Email:</strong> <a href="mailto:up202108872@fe.up.pt">up202108872@fe.up.pt</a></p>
                </li>
                <li>
                    <p><strong>Name</strong>José Luiz Caribé</p>
                    <p><strong>Email:</strong> <a href="mailto:up202103344@fe.up.pt">up202103344@fe.up.pt</a></p>
                </li>
                <li>
                    <p><strong>Name</strong>Xavier Outeiro</p>
                    <p><strong>Email:</strong> <a href="mailto:up202108895@fe.up.pt">up202108895@fe.up.pt</a></p>
                </li>
            </ul>
        </div>
    </div>
@endsection
