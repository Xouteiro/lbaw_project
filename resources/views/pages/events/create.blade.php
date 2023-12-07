@extends('layouts.nothome')

@section('content')
    <div class="container">
        <h1 style="text-align: center">Create Event</h1>
        <form class="general" method="POST" action="{{ route('event.store') }}">
            @csrf

            <label for="name">Name:</label>
            <input type="text" name="name" required>
            @if ($errors->has('name'))
                <span class="error">
                    {{ $errors->first('name') }}
                </span>
            @endif

            <label for="date">Event Date:</label>
            <input type="date" id="date" name="date" required>
            @if ($errors->has('date'))
                <span class="error">
                    {{ $errors->first('date') }}
                </span>
            @endif

            <label for="time">Event Time:</label>
            <input type="time" id="time" name="time" required>
            @if ($errors->has('time'))
                <span class="error">
                    {{ $errors->first('time') }}
                </span>
            @endif

            <label for="description">Description:</label>
            <textarea name="description" required></textarea>
            @if ($errors->has('description'))
                <span class="error">
                    {{ $errors->first('description') }}
                </span>
            @endif

            <label for="price">Price:</label>
            <input type="number" name="price" required>
            @if ($errors->has('price'))
                <span class="error">
                    {{ $errors->first('price') }}
                </span>
            @endif

            <label for="public">Public:</label>
            <input type="checkbox" name="public">
            

            <label for="opentojoin">Open to Join:</label>
            <input type="checkbox" name="opentojoin">

            <label for="capacity">Capacity:</label>
            <input type="number" name="capacity" required>
            @if ($errors->has('capacity'))
                <span class="error">
                    {{ $errors->first('capacity') }}
                </span>
            @endif

            <label for="id_location">Location:</label>

            <select name="id_location" required>
                <?php $locations = DB::table('location')->get(); ?>
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                @endforeach
            </select>

            <a href="{{ url('/user/' . Auth::user()->id) }}">
                <button type="button">Cancel</button>
            </a>

            <button type="submit">Create Event</button> 
        </form>
    </div>
@endsection
