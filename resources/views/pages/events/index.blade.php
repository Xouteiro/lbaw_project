@extends('layouts.app')

@section('content')
<div class="back-button-container">
{{-- <a class = 'back-button' href="{{ route('events') }}"><img class= 'back-button' src="{{ asset('icons/back.png') }}" alt="Back button"></a> --}}
</div>
    <div class="search-container">
        <div class="form">
            <form id="searchForm" action="{{ route('events.search') }}" method="GET">
                <input name="search" value="{{ request('search') }}" placeholder="Search again" class="search-event"/>
                <div class="input_field">
                    <label for="date">After:</label>
                    <input type="date" name="date" id="date" value="{{ request('date') }}">
                </div>
                <div class="input_field">
                    <select name="id_location" >
                        <option value="">Choose your location</option>
                        {{ $locations = DB::table('location')->get(); }}
                        @foreach ($locations as $location)
                            <option value="{{ $location->id }}" {{ request('id_location') == $location->id ? 'selected' : '' }}>
                                {{ $location->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="input_field">
                    <label for="free">Free</label>
                    <input type="checkbox" name="free"  value="free" {{ request('free') ? 'checked' : '' }}>
                </div>
                <div class="input_field">
                    <label for="finished">Finished</label>
                    <input type="checkbox" name="finished" value="finished" {{ request('finished') ? 'checked' : '' }}>
                </div>
                <button type="submit" id="searchButton">Search</button>
            </form>
        </div>
        <div class="container-events">
            @if(!$events->count())
                <p>No events were found for your search</p>
            @else
                <h1>Search Results:</h1>
            @endif
            <div class="events-container" id="eventsContainer"> <!--é aqui o infinite-->
                @each('partials.event_card', $events, 'event')
            </div>
        </div>
    </div>
@endsection
