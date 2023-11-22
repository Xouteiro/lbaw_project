@extends('layouts.app')

@section('content')
    <div class="container">
        <form class="general" action="{{ route('user.update', ['id' => $user->id]) }}" method="POST">
            @csrf

            <label for="email">Email</label>
            <input type="email" name="email" value="{{ $user->email }}" required>
            @if ($errors->has('email'))
                    <span class="error">
                        {{ $errors->first('email') }}
                    </span>
            @endif

            <label for="username">Username</label>
            <input type="text" name="username" value="{{ $user->username }}" required>
            @if ($errors->has('username'))
                <span class="error">
                    {{ $errors->first('username') }}
                </span>
            @endif

            <label for="name">Name</label>
            <input type="text" name="name" value="{{ $user->name }}" required>
            @if ($errors->has('name'))
                <span class="error">
                    {{ $errors->first('name') }}
                </span>
            @endif

            <label for="description">Description</label>
            <input type="text" name="description" value="{{ $user->description }}">
            @if ($errors->has('description'))
                <span class="error">
                    {{ $errors->first('description') }}
                </span>
            @endif

            <label for="password">New Password</label>
            <input type="password" name="password">
            @if ($errors->has('password'))
                <span class="error">
                    {{ $errors->first('password') }}
                </span>
            @endif

            <label for="password_confirmation">Confirm New Password</label>
            <input type="password" name="password_confirmation">
            

            <a href="{{ route('user.show', ['id' => $user->id]) }}">
                <button type="button">Cancel</button>
            </a>

            <button type="submit">Save Changes</button>
        </form>
    </div>
@endsection
