<h3>Hello, {{ $name }}</h3>
<h3>The event {{ $event }} has been updated!</h3>
<h3>Check out the new information for this event <a href="{{ route('event.show', ['id' => $eventId]) }}">here</a></h3>
<h4>Best regards,</h4>
<h4>Invents Staff</h4>