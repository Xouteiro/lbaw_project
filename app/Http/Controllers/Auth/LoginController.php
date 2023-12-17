<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\Email;
use Illuminate\Http\Request;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect('/home');
        } else {
            return view('auth.login');
        }
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended('/home');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home')
            ->withSuccess('You have logged out successfully!');
    }

    public function showForgetPassword()
    {
        return view('auth.forget-password');
    }

    public function sendEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors([
                'email' => 'The provided email does not match our records.',
            ])->onlyInput('email');
        }

        $passwordRecover = DB::table('password_recovers')
            ->where('email', $user->email)->first();

        if($passwordRecover){
            return back()->withErrors([
                'email' => 'You have already requested a password reset. Please check your email.',
            ])->onlyInput('email');
        }

        $token = Str::random(64);

        DB::table('password_recovers')->insert([
            'email' => $user->email,
            'token' => $token,
            'date' => date('Y-m-d H:i:s')
        ]);

        $data = array(
            'type' => 'password-recover',
            'name' => $user->name,
            'token' => $token
        );

        Mail::to($user->email, $user->name)->send(new Email($data));

        // Mail::send('partials.mail', $data, function ($message) use ($user) {
        //     $message->subject('Recover your password!');
        //     $message->from('invents@gmail.com', 'Invents Staff');
        //     $message->to($user->email, $user->name);
        // });

        return back()->with('success', "We have sent you an email with a link to reset your password.");
    }

    public function showPasswordRecover()
    {
        return view('auth.password-recover', ['token' => request()->route('token')]);
    }

    public function recoverPassword(Request $request)
    {
        $request->validate(
            [
                'token' => 'required',
                'password' => 'required|confirmed|min:6',
                'password_confirmation' => 'required|min:6'
            ],
            [
                'password.confirmed' => 'Password confirmation does not match',
                'password.min' => 'Password must be at least 6 characters'
            ]
        );

        $passwordRecover = DB::table('password_recovers')
            ->where('token', $request->token)->first();

        if (!$passwordRecover) {
            return back()->withErrors([
                'error' => 'Invalid token',
            ])->onlyInput('error');
        }

        $user = User::where('email', $passwordRecover->email)->first();
        $user->update([
            'password' => bcrypt($request->password)
        ]);

        DB::table('password_recovers')
            ->where('token', $request->token)
            ->delete();

        return redirect()->route('login')->with('success', "Your password has been changed successfully!");
    }
}
