<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\LikesDislikes;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function show(string $id)
    {
        if(Auth::check()){
            $user = User::findOrFail(Auth::user()->id);
            if($user->blocked){
                return redirect()->route('home');
            }
        }
        $comment = Comment::findOrFail($id);

        return view('partials.comment', [
            'comment' => $comment
        ]);
    }

    public function store(Request $request)
    {
        if(Auth::check()){
            $user = User::findOrFail(Auth::user()->id);
            if($user->blocked){
                return redirect()->route('home');
            }
        }
        $comment = new Comment();
        $comment->id_event = $request->id_event;
        $comment->id_user = $request->id_user;
        $comment->text = $request->comment;
        $comment->date = date('Y-m-d H:i:s');
        $this->authorize('store', $comment);
        $comment->save();

        $username = $comment->user->username;
        $eventOwner = $comment->event->id_owner;

        return response()->json(['id' => $comment->id, 'id_user' => $comment->id_user, 'text' => $comment->text, 'date' => $comment->date, 'username' => $username, 'owner' => $eventOwner, 'message' => 'Comment created successfully'], 200);;
    }

    public function update(Request $request, $id)
    {
        if(Auth::check()){
            $user = User::findOrFail(Auth::user()->id);
            if($user->blocked){
                return redirect()->route('home');
            }
        }
        $request->validate([
            'comment' => 'required|string|max:5000',
        ],
        [
            'comment.max' => 'Comment cannot be longer than 5000 characters!',
        ]);

        $comment = Comment::find($id);

        

        $comment->text = $request->comment;
        $this->authorize('update', $comment);
        $comment->save();
        return response()->json(['message' => 'You have successfully updated the comment!'], 200);
    }

    public function delete(string $id)
    {
        if(Auth::check()){
            $user = User::findOrFail(Auth::user()->id);
            if($user->blocked){
                return redirect()->route('home');
            }
        }
        $comment = Comment::find($id);

        $this->authorize('delete', $comment);

        $comment->delete();
        return response()->json(['message' => 'You have successfully deleted the comment!'], 200);
    }

    public function likeComment(Request $request)
    {
        if(Auth::check()){
            $user = User::findOrFail(Auth::user()->id);
            if($user->blocked){
                return redirect()->route('home');
            }
        }
        $comment = Comment::find($request->id_comment);
        $likesDislikes = LikesDislikes::where('id_comment', $request->id_comment)->where('id_user', $request->id_user)->first();
        
        if($request->action == 'add'){
            $comment->likes++;
            if($likesDislikes == null){
                $likesDislikes = new LikesDislikes();
                $likesDislikes->id_comment = $request->id_comment;
                $likesDislikes->id_user = $request->id_user;
            }
            else if($likesDislikes->liked == 0){
                $comment->dislikes--;
            }
            $likesDislikes->liked = 1;
            $likesDislikes->save();
        }
        else {
            $comment->likes--;
            $likesDislikes->delete();
        }

        $comment->save();
        return response()->json(['message' => 'You have successfully liked the comment!'], 200);
    }

    public function dislikeComment(Request $request)
    {
        if(Auth::check()){
            $user = User::findOrFail(Auth::user()->id);
            if($user->blocked){
                return redirect()->route('home');
            }
        }
        $comment = Comment::find($request->id_comment);
        $likesDislikes = LikesDislikes::where('id_comment', $request->id_comment)->where('id_user', $request->id_user)->first();

        if($request->action == 'add'){
            $comment->dislikes++;
            if($likesDislikes == null){
                $likesDislikes = new LikesDislikes();
                $likesDislikes->id_comment = $request->id_comment;
                $likesDislikes->id_user = $request->id_user;
            }
            else if($likesDislikes->liked == 1){
                $comment->likes--;
            }
            $likesDislikes->liked = 0;
            $likesDislikes->save();
        }
        else {
            $comment->dislikes--;
            $likesDislikes->delete();
        }

        $comment->save();
        return response()->json(['message' => 'You have successfully disliked the comment!'], 200);
    }
}
