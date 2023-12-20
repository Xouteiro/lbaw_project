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
                        <?php $locations = DB::table('location')->get(); ?>
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
                <div class="input_field">
                    <label for="opentojoin">Open to join</label>
                    <input type="checkbox" name="opentojoin" value="opentojoin" {{ request('opentojoin') ? 'checked' : '' }}>
                </div>

                <div class="order input_field">
                    <select name="order" id="order">
                        <option value="">Order By</option>
                        <option value="eventdate-desc" {{ request('order') == 'eventdate-desc' ? 'selected' : '' }}>Date (Furthest from Today) </option>
                        <option value="eventdate-asc" {{ request('order') == 'eventdate-asc' ? 'selected' : '' }}>Date (Closest to Today) </option>
                        <option value="price-desc" {{ request('order') == 'price-desc' ? 'selected' : '' }}>Price (High to Low)</option>
                        <option value="price-asc" {{ request('order') == 'price-asc' ? 'selected' : '' }}>Price (Low to High)</option>
                        <option value="capacity-desc" {{ request('order') == 'capacity-desc' ? 'selected' : '' }}>Capacity (High to Low)</option>
                        <option value="capacity-asc" {{ request('order') == 'capacity-asc' ? 'selected' : '' }}>Capacity (Low to High)</option>
                    </select>
                </div>
                <button type="submit" id="searchButton">Search</button>
            </form>
        </div>
        <div class="container-events">
            @php
                $filters = [];
                if(request('search') != null) {
                    $filters[] = "events found for '" . request('search') . "'";
                }
                if(request('date') != null) {
                    $filters[] = "after " . request('date') . "";
                }
                if(request('id_location') != null) {
                    $filters[] = " in " . DB::table('location')->where('id', request('id_location'))->value('name') . "";
                }
                if(request('free') != null) {
                    $filters[] = "free ";
                }
                if(request('finished') != null) {
                    $filters[] = "finished ";
                }
                if(request('opentojoin') != null) {
                    $filters[] = "open to join ";
                }
            @endphp
            @if(!empty($filters))
            <h3>{{ ucfirst(implode(', ', $filters)) }} events :</h3>
            @endif
            <div class="events-container" id="eventsContainer" data-query="{{ http_build_query(request()->query()) }}"> 
            </div>
        </div>
    </div>
@endsection
