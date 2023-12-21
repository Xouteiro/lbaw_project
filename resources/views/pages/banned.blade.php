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
            <header class="home">
                <a class="logo" href={{url('/home')}}><img class="logo" src="{{ url('icons/logo.png') }}" alt="Invents"></a>  
                <a class="user" href=""><span>{{ Auth::user()->name }}</span></a>
                <a href=""><img class="user" src="{{ Auth::user()->getProfileImage() }}"></a>
            </header>
            <section id="content" class="home">
                <div class="container banned-page">
                    <div class="banned-text">
                        <h1>You're banned!</h1>
                        <h2>Sorry, but you're banned from this website.</h2>
                        <h3>For more information, please contact the website's administrator.</h3>
                    </div>
                </div>
            </section>
            <footer>
                <div class="useful-links">
                    <h3>Useful Links</h3>
                    <a href="{{route('home')}}">- Home Page</a>
                    <a href="{{route('logout')}}">- Logout</a>
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
                    <a href="{{route('about')}}">- About Us</a>
                </div>
            </footer>
        </main>
    </body>
</html>