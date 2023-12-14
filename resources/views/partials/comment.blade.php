<li>
    <div id="{{ $comment->id }}" class="comment">
        <div class="comment-header">
            <div class="comment-header-title-likes">
                @if(isset($comment->user->username)) 
                    <h3>{{ $comment->user->username }}</h3>
                @else
                    Anonymous
                @endif
                <div class="likes-dislikes">
                    <p class="comment-like-number">{{ $comment->likes }}</p>
                    <img id="{{ Auth::user()->id }}" class="comment-like" src="{{ url('icons/like.png') }}" alt="like">
                    <p class="comment-dislike-number">{{ $comment->dislikes }}</p>
                    <img id="{{ Auth::user()->id }}" class="comment-dislike" src="{{ url('icons/like.png') }}" alt="dislike">
                </div>
            </div>
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
        <p>{{ $comment->text }}</p>
        <p>{{ $comment->date }}</p>
    </div>
</li>