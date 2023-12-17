<h3>Hello, {{ $name }}</h3>
<h3>You have been invited to the event {{ $event }}</h3>
<h3>You can accept or deny this invitation <a href="{{ route('event.show', ['event' => $eventId, 'invite' => $inviteId]) }}">here</a></h3>
<h4>Best regards,</h4>
<h4>Invents Staff</h4>