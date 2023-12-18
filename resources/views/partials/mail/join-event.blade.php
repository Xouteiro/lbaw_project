<h3>Hello, {{ $name }}</h3>
<h3>Thank you for joining the event {{ $event }}</h3>
<h3>Check out the event's page <a href="{{ route('event.show', ['id' => $eventId]) }}">here</a></h3>
<h4>Best regards,</h4>
<h4>Invents Staff</h4>