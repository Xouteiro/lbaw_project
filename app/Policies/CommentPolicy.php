<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    public function store(User $auth, Comment $comment)
    {
        return $auth->id == $comment->id_user && !$auth->blocked;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Comment $comment): bool
    {
        return ($user->id === $comment->user->id && !$user->blocked) || $user->admin;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Comment $comment): bool
    {
        return ($user->id === $comment->user->id && !$user->blocked) || $user->admin;
    }
}
