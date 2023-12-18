<h3>Hello, {{ $name }}</h3>
<h3>You can now reset your password <a href="{{ route('password.recover.show', ['token' => $token]) }}">here</a></h3>
<h4>Best regards,</h4>
<h4>Invents Staff</h4>