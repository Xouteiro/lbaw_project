<li>
    <h3>{{ $poll->title }}</h3>
    <ul>
        @foreach ($poll->options as $option)
            <li>
                <label>
                    <input type="radio" name="poll_option" value="{{ $option->id }}">
                    {{ $option->name }} -
                    {{ $option->voters->count() }}
                </label>
            </li>
        @endforeach
    </ul>
    <button type="submit">Vote</button>
</li>
