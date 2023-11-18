@extends('layouts.app')

@section('content')
<form action="{{ route('user.update', ['id' => $user->id]) }}" method="POST">
    @csrf

    <label for="email">Email</label>
    <input type="email" name="email" value="{{ $user->email }}" required>

    <label for="username">Username</label>
    <input type="text" name="username" value="{{ $user->username }}" required>

    <label for="name">Name</label>
    <input type="text" name="name" value="{{ $user->name }}" required>

    <label for="description">Description</label>
    <input type="text" name="description" value="{{ $user->description }}">

    <label for="password">New Password</label>
    <input type="password" name="password">

    <!--  
    <label for="password_confirmation">Confirm New Password</label>
    <input type="password" name="password_confirmation">
    -->


    <a href="{{ route('user.show', ['id' => $user->id]) }}">
        <button type="button">Cancel</button>
    </a>


    <button type="submit">Save Changes</button>

    
</form>


@endsection
