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

        $this->authorize('show', $user);

        return view('pages.user', [
            'user' => $user
        ]);
    }

    public function edit(string $id)
    {
        $user = User::findOrFail($id);

        $this->authorize('edit', $user);

        return view('pages.user_edit', [
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
            'description' => 'string|max:1000',
            'password' => 'nullable|min:8',
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

        $this->authorize('delete', $user);

        $user->delete();
        return redirect()->route('home')
            ->withSuccess('You have successfully deleted your profile!');
    }

    public function joinEvent(string $id)
    {
        $event = Event::findOrFail($id);
        if (Auth::check()) {
            $user = User::findOrFail(Auth::id());
            if ($event->openToJoin || true) { //TODO Verificar se user foi convidado
                //TODO Adcionar user ao evento
            }
        }
    }
}
