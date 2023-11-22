<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function show(string $id)
    {
        $user = User::findOrFail($id);
        $this->authorize('show',$user);
        
        return view('pages.users.show', [
            'user' => $user
        ]);
    }

    public function edit(string $id)
    {
        $user = User::findOrFail($id);

        $this->authorize('update', $user);

        return view('pages.users.edit', [
            'user' => $user
        ]);
    }


    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $this->authorize('update', $user);

        $request->validate([
            'email' => 'required|email|max:250|unique:users,email,' . $id,
            'username' => 'required|string|max:250|unique:users,username,' . $id,
            'name' => 'required|string|max:250|unique:users,name,' . $id,
            'description' => 'string|max:2000',
            'password' => 'nullable|min:8|confirmed',
        ],[
            'email.unique' => 'This email is already in use.',
            'username.unique' => 'This username is already in use.',
            'name.unique' => 'This name is already in use.',
            
        ]);

        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        $user->email = $request->email;
        $user->username = $request->username;
        $user->name = $request->name;
        $user->description = $request->description;

        $user->save();

        return redirect()->route('user.show', ['id' => $user->id])
            ->withSuccess('You have successfully edited your profile!');
    }

    public function delete(string $id) //fazer com que os eventos/comentarios/etc nao sejam apagados
    {
        $user = User::findOrFail($id);

        $this->authorize('update',$user);

        $user->delete();
        return redirect()->route('home')
            ->withSuccess('You have successfully deleted your profile!');
    }

    public function manageEvent(Request $request, string $id_event)
    {
        $user = User::find(Auth::user()->id);
        $event = Event::findOrFail($id_event);

        $this->authorize('manageEvent', $event);
        if($request->events == 'created') {
            if($request->actionName == 'pin'){
                $pinAction = filter_var($request->input('pinAction'), FILTER_VALIDATE_BOOLEAN);
                $event->update([
                    'highlight_owner' => $pinAction,
                    'hide_owner' => false
                ]);
            }
            else if($request->actionName == 'hide'){
                $hideAction = filter_var($request->input('hideAction'), FILTER_VALIDATE_BOOLEAN);
                $event->update([
                    'highlight_owner' => false,
                    'hide_owner' => $hideAction,
                ]);
            }
        }
        else if($request->events == 'joined') {
            if($request->actionName == 'pin'){
                $pinAction = filter_var($request->input('pinAction'), FILTER_VALIDATE_BOOLEAN);
                $user->events()->updateExistingPivot($id_event, [
                    'highlighted' => $pinAction,
                    'hidden' => false
                ]);
            }
            else if($request->actionName == 'hide'){
                $hideAction = filter_var($request->input('hideAction'), FILTER_VALIDATE_BOOLEAN);
                $user->events()->updateExistingPivot($id_event, [
                    'highlighted' => false,
                    'hidden' => $hideAction,
                ]);
            }
        }

        return response()->json(['message' => 'Update successful'], 200);
    }
}
