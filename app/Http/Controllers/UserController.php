<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);

        //authorize->policies

        return view('pages.user', [
            'user' => $user
        ]);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        //authorize edit

        $request->validate([
            'email' => 'required|email|max:250|unique:users',
            'username' => 'required|string|max:250|unique:users',
            'name' => 'required|string|max:250',
            'description' => 'string|max:250'
        ]);

        if($request->password){
            $request->validate([
                'password' => 'required|min:8|confirmed'
            ]);
            $user->password = Hash::make($request->password);
        }

        $user->email = $request->email;
        $user->username = $request->username;
        $user->name = $request->name;
        $user->description = $request->description;

        $user->save();
        return redirect()->route('user.show', ['id' => $user->id])
            ->withSuccess('You have successfully edited your profile!');

    }


    /**
     * Remove the specified resource from storage.
     */
    public function delete(string $id) //fazer com que os eventos/comentarios/etc nao sejam apagados
    {
        $user = User::findOrFail($id);
        //authorize delete
        $user->delete();
        return redirect()->route('home')
            ->withSuccess('You have successfully deleted your profile!');
        
    }
}