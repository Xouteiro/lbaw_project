@extends('layouts.app')
@section('content')
    <div class="container">
        <h1>Edit Event</h1>
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
                <select name="id_location" required>
                    <?php $locations = DB::table('location')->get(); ?>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}" {{ $location->id == $event->id_location ? 'selected' : '' }}>{{ $location->name }}</option>
                    @endforeach
                </select>

            <a href="{{ route('event.show', ['id' => $event->id]) }}">
                <button type="button">Cancel</button>
            </a>

            <button type="submit" class="btn btn-primary">Update Event</button>
        </form>
    </div>
@endsection

