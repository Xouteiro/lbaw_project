@extends('layouts.app')

@section('content')
    <div class="container home-page">
        <h1>Welcome to home page of Invents!</h1>
        <h1><a href="{{ route('events') }}">Check out the available events!</a></h1>
        <p>Still in construction</p>
    </div>
@endsection
