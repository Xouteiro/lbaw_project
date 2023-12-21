@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 style="text-align: center">Edit Event</h1>
            <div class="profile-picture">
                <img src="{{ $event->getEventImage($event->id) }}">
                <div class="profile-picture-buttons">
                    <form method="POST" action="{{ route('file.upload') }}" enctype="multipart/form-data">
                        @csrf
                        <input name="file" type="file" required>
                        <input name="id" type="number" value="{{ $event->id }}" hidden>
                        <input name="type" type="text" value="event" hidden>
                        <button type="submit">Submit</button>
                    </form>
                    <form method="POST" action="{{ route('file.deleteEventPicture') }}" enctype="multipart/form-data">
                        @csrf
                        <input name="id" type="number" value="{{ $event->id }}" hidden>
                        <button type="submit">Remove</button>
                    </form>
                </div>
            </div>
        <form class="general" action="{{ route('event.update', ['id' => $event->id]) }}" method="POST">
            @csrf
            <label for="name">Name</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $event->name) }}" required/>
            @if ($errors->has('name'))
                <span class="error">
                    {{ $errors->first('name') }}
                </span>
            @endif

            <label for="eventdate">Event Date</label>
            <input type="datetime-local" class="form-control" id="eventdate" name="eventdate" value="{{ date('Y-m-d\TH:i', strtotime($event->eventdate)) }}" required/>
            @if ($errors->has('eventdate'))
                <span class="error">
                {{ $errors->first('eventdate') }}
                </span>
            @endif
        
            <label for="description">Description</label>
            <textarea class="form-control" id="description" name="description" required>{{ $event->description }}</textarea>
            @if ($errors->has('description'))
                <span class="error">
                    {{ $errors->first('description') }}
                </span>
            @endif
        
            <label for="price">Price</label>
            <input type="number" class="form-control" id="price" name="price" value="{{ $event->price }}" required>
            @if ($errors->has('price'))
                <span class="error">
                    {{ $errors->first('price') }}
                </span>
            @endif

            <label for="public">Public:</label>
            <input type="checkbox" name="public" value="1" {{ $event->public ? 'checked' : '' }}>

        
            <label for="opentojoin">Open to Join:</label>
            <input type="checkbox" name="opentojoin" value="1" {{ $event->opentojoin ? 'checked' : '' }}>

        
            <label for="capacity">Capacity</label>
            <input type="number" class="form-control" id="capacity" name="capacity" value="{{ $event->capacity }}" required>
            @if ($errors->has('capacity'))
                <span class="error">
                    {{ $errors->first('capacity') }}
                </span>
            @endif
            <label for="id_location">Location</label>
            <div class="full-location">
                <select class="location-select" name="id_location" required>
                    <?php $locations = DB::table('location')->get(); ?>
                    <option value="" disabled >Select a location</option>
                    @foreach ($locations as $location)
                        @if($location->id == 79)
                            @continue
                        @endif
                        <option value="{{ $location->id }}" {{ $location->id == $event->id_location ? 'selected' : '' }}>{{ $location->name }}</option>
                    @endforeach
                </select>
                <button type="button" class="fake-add-location">Add Location</button>
            </div>
            <a class="button" href="{{ route('event.show', ['id' => $event->id]) }}">Cancel</a>
            <button id="{{ $event->id }}" type="submit" class="btn btn-primary">Update Event</button>
        </form>
    </div>
@endsection

