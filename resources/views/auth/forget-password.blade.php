@extends('layouts.app')

@section('content')
<div class="container">
    <h1 style="text-align: center">Recover Password</h1>
    <form class="general" method="POST" action="{{ route('send.email') }}">
        @csrf
        
        @if(session('success'))
            <p class="success">
                {{ session('success') }}
            </p>
        @endif
        @if ($errors->has('email'))
                <span class="error">
                {{ $errors->first('email') }}
                </span>
        @endif
        <label for="email">Your email</label>
        <input id="email" type="email" name="email" placeholder="Email" required>
        <button type="submit">Send Email</button>
    </form>
</div>
@endsection
