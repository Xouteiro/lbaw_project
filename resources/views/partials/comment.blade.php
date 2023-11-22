<li>
    <h3>{{ $comment->user->username }}</h3>
    @if (Auth::check() && (Auth::id() === $comment->user->id || Auth::user()admin))
        <form action="" method="POST">
            <button class="delete_comment_button" type="submit">
                <p> Delete comment</p>
            </button>
        </form>
        <form action="" method="POST">
            <button class="edit_comment_button" type="submit">
                <p> Edit comment</p>
            </button>
        </form>
    @endif
    <p>{{ $comment->text }}</p>
</li>