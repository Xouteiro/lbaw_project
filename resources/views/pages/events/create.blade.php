@extends('layouts.app')

@section('content')
<form method="POST" action="{{ route('event.store') }}">
    @csrf

    <label for="name">Name:</label>
    <input type="text" name="name" required>

    <label for="date">Event Date:</label>
    <input type="date" id="date" name="date" required>

    <label for="time">Event Time:</label>
    <input type="time" id="time" name="time" required>


    <label for="description">Description:</label>
    <textarea name="description" required></textarea>

    <label for="price">Price:</label>
        <input type="number" name="price" required>

        <label for="public">Public:</label>
        <input type="checkbox" name="public" >

        <label for="opentojoin">Open to Join:</label>
        <input type="checkbox" name="opentojoin" >

        <label for="capacity">Capacity:</label>
        <input type="number" name="capacity" required>

        <label for="id_location">Location:</label>

        <select name="id_location" required>
            <?php $locations = DB::table('location')->get(); ?>
            @foreach ($locations as $location)
                <option value="{{ $location->id }}">{{ $location->name }}</option>
            @endforeach
        </select>

        <button type="submit">Create Event</button>
    </form>
    @endsection
