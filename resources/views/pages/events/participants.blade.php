@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>{{$event->name}} Participants</h1>
        <div class="participants">
            @if($event->participants->count() == 0)
                <p>No participants yet</p>
                <!-- invite button -->
            @endif
            @foreach($event->participants as $participant)
                @include('partials.participant', ['participant' => $participant])
            @endforeach
        </div>
    </div>
@endsection
