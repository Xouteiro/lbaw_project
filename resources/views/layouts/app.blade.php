<?php
    use Illuminate\Support\Facades\Auth;
    use App\Models\Notification;

    if(Auth::check()){
        $id = Auth::user()->id;
        // get all types of notifications
        $invites = Notification::where('event_notification.id_user', $id)
        ->join('invite',
        'invite.id_eventnotification', '=', 'event_notification.id')
        ->get();
        $eventUpdates = Notification::where('event_notification.id_user', $id)
        ->join('event_update',
        'event_update.id_eventnotification', '=', 'event_notification.id')
        ->get();
        $requestsToJoin = Notification::where('event_notification.id_user', $id)
        ->join('request_to_join',
        'request_to_join.id_eventnotification', '=', 'event_notification.id')
        ->get();

        // join all notifications
        $notifications = [$invites, $eventUpdates, $requestsToJoin];
    }
?>
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/x-icon" href="/icons/logo.ico">
        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Styles -->
        <link href="{{ url('css/app.css') }}" rel="stylesheet">
        <script type="text/javascript">
            // Fix for Firefox autofocus CSS bug
            // See: http://stackoverflow.com/questions/18943276/html-5-autofocus-messes-up-css-loading/18945951#18945951
        </script>
        <script type="text/javascript" src={{ url('js/app.js') }} defer>
        </script>
    </head>
    <body>
        <main>
            @if( url()->current() == url('/home') || url()->current() == url('/about') || url()->current() == url('/mainFeatures') )
            <header class="home">
                <a class="logo" href={{url('/home')}}><img class="logo" src="{{ url('icons/logo.png') }}" alt="Invents"></a>  
            @else  
            <header>
                <a class="small-logo" href={{url('/home')}}><img class="small-logo" src="{{ url('icons/logo.png') }}" alt="Invents"></a> 
            @endif               
                <form class="searchBar" id="searchForm" action="{{ route('events.search') }}" method="GET">
                    <input name="search" value="" placeholder="Search event" class="search-event"/>
                    <button type="submit" id="searchButton"></button>
                </form> 
                @if (Auth::check())
                    @if(!Auth::user()->admin)
                        <img class="notifications-icon" src="{{url('icons/bell.png')}}" alt="Notifications Image">
                        <div class="user-notifications-container" style="display: none;">
                            <div class="user-notifications">
                                <h2 class="notification">Invites</h2>
                                <div class="invites notification">
                                    @if($notifications[0]->count() == 0)
                                        <h4 class="notification">No invites</h4>
                                    @endif
                                    @foreach($notifications[0] as $invite)
                                        <a class="pending_invite notification" href="{{ url($invite->link) . '?id_invite=' . $invite->id}}">
                                            <h4 class="notification">- {{$invite->text}}</h4>
                                        </a>
                                    @endforeach
                                </div>
                                <h2 class="notification">Requests To Join Your Events</h2>
                                <div class="requests-to-join notification">
                                    @if($notifications[2]->count() == 0)
                                        <h4 class="notification">No Requests To Join</h4>
                                    @endif
                                    @foreach($notifications[2] as $requestToJoin)
                                        <div class="pending_request_to_join notification" id="{{ $requestToJoin->id_eventnotification }}">
                                            <h4 class="notification">- {{$requestToJoin->text}}</h4>
                                        </div>
                                    @endforeach
                                </div>
                                <h2 class="notification">Event Updates</h2>
                                <div class="event-updates notification">
                                    @if($notifications[1]->count() == 0)
                                        <h4 class="notification">No Event Updates</h4>
                                    @endif
                                    @foreach($notifications[1] as $eventUpdate)
                                        <div class="pending_event_update notification" id="{{ $eventUpdate->id_eventnotification }}">
                                            <a href="{{ url($eventUpdate->link) . '?id_eventUpdate=' . $eventUpdate->id }}" class="notification" id="{{ $eventUpdate->id_event }}">- {{$eventUpdate->text}}</a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                    <a class="user" href="{{ url('/user/' . Auth::user()->id) }}"><span>{{ Auth::user()->name }}</span></a>
                    <a href="{{ url('/user/' . Auth::user()->id) }}"><img class="user" src="{{ Auth::user()->getProfileImage() }}"></a>
                @elseif (request()->path() !== 'login')
                    <a class="button user" href="{{ url('/login') }}"> Login </a> 
                @endif
            </header>
            @if( url()->current() == url('/home') )
            <section id="content" class="home">
            @elseif( url()->current() == url('/about') )
            <section id="content" class="about">
            @elseif( url()->current() == url('/mainFeatures') )
            <section id="content" class="mainFeatures">
            @else
            <section id="content">
            @endif
                @yield('content')
            </section>
            <footer>
                <div class="useful-links">
                    <h3>Useful Links</h3>
                    <a href="{{route('home')}}">- Home Page</a>
                    <a href="{{route('events')}}">- All Events</a>
                    @if(Auth::check())
                        <a href="{{ url('/user/' . Auth::user()->id) }}">- Profile Page</a>
                    @else
                        <a href="{{route('login')}}">- Profile Page</a>
                    @endif
                    @if(Auth::check() && !Auth::user()->admin)
                        <a href="{{route('event.create')}}">- Create an Event</a>
                    @elseif(!Auth::check())
                        <a href="{{route('login')}}">- Create an Event</a>
                    @endif
                    @if(Auth::check())
                        <a href="{{route('logout')}}">- Logout</a>
                    @endif

                </div>

                <div class="media-logo">
                    <a class="image" href={{url('/home')}}><img class="logo" src="{{ url('icons/logo.png') }}" alt="Invents"></a>
                    <div class = "social-copy">
                        <div class="social-media">
                            <img src="{{ url('icons/instagram.png') }}" alt="Instagram icon">
                            <img src="{{ url('icons/facebook.png') }}" alt="Facebook icon">
                            <img src="{{ url('icons/twitter.png') }}" alt="Twitter icon">
                        </div>
                        <p>© 2023 Invents</p>
                    </div>
                </div>

                <div class="static-pages">
                    <h3>Information</h3>
                    <a href="{{route('mainFeatures')}}">- Main Features</a>
                    <a href="{{route('about')}}"> - About Us</a>

                </div>
    
            </footer>
        </main>
    </body>
</html>