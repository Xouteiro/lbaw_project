<li>
    <div id="{{ $comment->id }}" class="comment">
        @if(isset($comment->user->username)) 
            <h3>{{ $comment->user->username }}</h3>
        @else
            Anonymous
        @endif
        <p>{{ $comment->text }}</p>
        <p>{{ $comment->date }}</p>
        @if (Auth::check() && (Auth::user()->id === $comment->id_user || Auth::user()->admin))
            <div class="comment-actions">
                <button class="fake button edit-comment" id="{{ $comment->id }}">
                    Edit Comment
                </button>
                <button class="fake button delete-comment" id="{{ $comment->id }}">
                    Delete Comment
                </button>
            </div>
        @endif
    </div>
</li>