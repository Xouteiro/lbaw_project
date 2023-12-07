<li>
    <div class="comment">
        @if(isset($comment->user->username)) 
            <h3>{{ $comment->user->username }}</h3>
        @else
            Anonymous
        @endif
    @if (Auth::check() && (Auth::user()->id === $comment->id_user || Auth::user()->admin))
        <form action="" method="POST">
            <button class="edit_comment_button" type="submit">
                <p>Edit comment</p>
            </button>
        </form>
        <form action="" method="POST">
            <button class="delete_comment_button" type="submit">
                <p>Delete comment</p>
            </button>
        </form>
    @endif
    </div>
    <p>{{ $comment->text }}</p>
</li>