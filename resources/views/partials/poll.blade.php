<li class="poll" >
    <div class="poll-header">
        <h3>{{ $poll->title }}</h3>
        <button type="button" class="fake-poll-delete-button">&#10060;</button>
    </div>
    <ul class='poll-options' >
        @foreach ($poll->options as $option)
            <li class='poll-option'>
                <label>
                    <input type="radio" name="poll_option {{$poll->id}}" value="{{ $option->id }}">
                    <p> {{ $option->name }} - {{ $option->voters->count() }} </p>
                </label>
            </li>
        @endforeach
    </ul>
</li>
