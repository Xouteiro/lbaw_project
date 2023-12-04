<h3>Hello, {{ $name }}</h3>
<h3>You can now reset your password <a href="{{ route('password.recover.show', ['token' => $token]) }}">here</a></h3>
<p>Best regards,</p>
<p>Invents Staff</p>