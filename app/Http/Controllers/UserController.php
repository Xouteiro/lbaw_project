<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);
        $this->authorize('show', $user);
        return view('pages.user', [
            'user' => $user
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        //dd($user->id);
        $this->authorize('edit', $user);

        return view('pages.user_edit', [
            'user' => $user
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
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
