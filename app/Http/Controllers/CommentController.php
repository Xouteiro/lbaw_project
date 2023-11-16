<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Comment;

class CommentController extends Controller
{
    public function show(string $id)
    {
        $comment = Comment::findOrFail($id);

        // $this->authorize('show', $comment);    is this necessary?

        return view('partials.comment', [
            'comment' => $comment
        ]);

    }

    public function create(Request $request, $id_event, $id_user)
    {
        $comment = new Comment();
        $comment->id_event = $id_event;
        $comment->id_user = $id_user;
        $comment->text = $request->input('text');
        $comment->date = date('Y-m-d');

        // $this->authorize('create', $comment);    is this necessary?

        $comment->save();
        return response()->json($comment);
    }

    public function update(Request $request, $id)
    {
        $comment = Comment::find($id);

        // $this->authorize('update', $comment); is this necessary?

        $comment->text = $request->input('text');

        $comment->save();
        return response()->json($comment);
    }

    public function delete(string $id)
    {
        $comment = Comment::find($id);

        // $this->authorize('delete', $comment); is this necessary?

        $comment->delete();
        return redirect()->route('event.show', ['id' => $comment->id_event])
        ->withSuccess('You have successfully deleted your comment!');
    }
}
