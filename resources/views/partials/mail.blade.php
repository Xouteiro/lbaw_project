<h1>Hello, {{ $name }}</h1>
<h1>You can now reset your password <a href="{{ route('password.recover.show', ['token' => $token]) }}">here</a></h1>
<p>Best regards,</p>
<p>Invents Staff</p>