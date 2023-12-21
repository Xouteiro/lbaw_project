@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Admin Candidates</h1>
        <div class="participants">
            @if($users->count() == 0)
                <h4>No candidates yet</h4>
            @else
            @foreach($users as $user)
                @include('partials.participant', ['user' => $user])
            @endforeach
            @endif
        </div>
    </div>
@endsection
