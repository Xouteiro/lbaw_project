<li>
    <h3>{{ $poll->title }}</h3>
    <ul>
        @foreach ($poll->options() as $option)
            <li>
                <p>{{ $option->name }}</p>
                <p>Número de votos: {{ $option->voters()->length() }}</p>
            </li>
        @endforeach
    </ul>
</li>
