<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;

class CommentController extends Controller
{
    public function show(string $id)
    {
        $comment = Comment::findOrFail($id);

        return view('partials.comment', [
            'comment' => $comment
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'comment' => 'required|string|max:5000',
        ],
        [
            'comment.max' => 'Comment cannot be longer than 5000 characters!',
        ]);

        $comment = new Comment();
        $comment->id_event = $request->id_event;
        $comment->id_user = $request->id_user;
        $comment->text = $request->comment;
        $comment->date = date('Y-m-d H:i:s');
        $comment->save();

        // $this->authorize('store');

        return redirect(url()->previous() . '#' . $comment->id)->with('success', 'You have successfully created a comment!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:5000',
        ],
        [
            'comment.max' => 'Comment cannot be longer than 5000 characters!',
        ]);

        $comment = Comment::find($id);

        // $this->authorize('update', $comment);

        $comment->text = $request->comment;

        $comment->save();
        return redirect(url()->previous() . '#' . $comment->id)->with('success', 'You have successfully updated your comment!');
    }

    public function delete(string $id)
    {
        $comment = Comment::find($id);

        // $this->authorize('delete', $comment);

        $comment->delete();
        return redirect()->route('event.show', ['id' => $comment->id_event])
        ->withSuccess('You have successfully deleted your comment!');
    }
}
