<li class="poll" >
    <div class="poll-header">
        <h4>{{ $poll->title }}</h3>
        <button  title="Delete Poll" type="button" class="fake-poll-delete-button no-button">&#128465;</button>
    </div>
    <ul class='poll-options' >
        @foreach ($poll->options as $option)
            <li class='poll-option'>
                <label>
                    <?php $user = Auth::user();?>
                    @if($user->pollOptions->contains($option->id))
                    <input type="radio" name="poll_option {{$poll->id}}" value="{{ $option->id }}" class="user_vote">
                    <p> {{ $option->name }} - {{ $option->voters->count() }} </p>
                    @else
                    <input type="radio" name="poll_option {{$poll->id}}" value="{{ $option->id }}">
                    <p> {{ $option->name }} - {{ $option->voters->count() }} </p>
                    @endif
                </label>
            </li>
        @endforeach
    </ul>
</li>
