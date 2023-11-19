@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Event</h1>
        <form action="{{ route('event.update', ['id' => $event->id]) }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $event->name) }}" required>
            </div>

            <div class="form-group">
                <label for="eventdate">Event Date</label>
                <input type="datetime-local" class="form-control" id="eventdate" name="eventdate" value="{{ date('Y-m-d\TH:i', strtotime($event->eventdate)) }}" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea class="form-control" id="description" name="description" required>{{ $event->description }}</textarea>
            </div>

            <div class="form-group">
                <label for="price">Price</label>
                <input type="number" class="form-control" id="price" name="price" value="{{ $event->price }}" required>
            </div>

            <div class="form-group">
                <label for="capacity">Capacity</label>
                <input type="number" class="form-control" id="capacity" name="capacity" value="{{ $event->capacity }}" required>
            </div>

            <div class="form-group">
                <label for="id_location">Location</label>
                <input type="text" class="form-control" id="id_location" name="id_location" value="{{ $event->id_location }}" required>
            </div>

            <button type="submit" class="btn btn-primary">Update Event</button>
        </form>
    </div>
@endsection
