@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 style="text-align: center">Edit Profile</h1>
            <div class="profile-picture">
                <img src="{{ $user->getProfileImage() }}">
            <div class="profile-picture-buttons">
            <form method="POST" action="{{ route('file.upload') }}" enctype="multipart/form-data">
                @csrf
                <input name="file" type="file" required>
                <input name="id" type="number" value="{{ $user->id }}" hidden>
                <input name="type" type="text" value="profile" hidden>
                <button type="submit">Submit</button>
            </form>   
            <form method="POST" action="{{ route('file.deleteProfilePicture') }}" enctype="multipart/form-data">
                @csrf
                <input name="id" type="number" value="{{ $user->id }}" hidden>
                <button type="submit">Remove</button>
            </form>
            </div>
            </div>     
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
            <textarea type="text" name="description">{{ $user->description }}</textarea>
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
