<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\User;

class RegisterController extends Controller
{
    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:250|unique:users',
            'username' => 'required|string|max:250',
            'name' => 'required|string|max:250',
            'password' => 'required|min:8|confirmed'
        ]);

        User::create([
            'email' => $request->email,
            'username' => $request->username,
            'name' => $request->name,
            'password' => bcrypt($request->password)
        ]);

        $credentials = $request->only('email', 'password');
        Auth::attempt($credentials);
        $request->session()->regenerate();
        return redirect()->route('home') //talvez mudar 
            ->withSuccess('You have successfully registered & logged in!');
    }
}
