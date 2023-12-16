<li class="poll">
    <h3>{{ $poll->title }}</h3>
    <ul class='poll-options'>
        @foreach ($poll->options as $option)
            <li class='poll-option'>
                <label>
                    <input type="radio" name="poll_option" value="{{ $option->id }}">
                    <p> {{ $option->name }} - {{ $option->voters->count() }} </p>
                </label>
            </li>
        @endforeach
    </ul>
</li>
