<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class StaticPagesController extends Controller
{
    public function home()
    {
        if (Auth::check()) {
            $user = User::findOrFail(Auth::user()->id);
            if($user->blocked){
                return view('pages.banned');
            }
            $events = Event::where('hide_owner', '=', false)->inRandomOrder()->get()->take(6);
            return view('pages.home', ['events' => $events]);
        } else {
            $events = Event::where([['hide_owner', '=', false], ['public', '=', true]])->inRandomOrder()->get()->take(6);
            return view('pages.home', ['events' => $events]);
        }
    }

    public function about()
    {
        return view('pages.about');
    }

    public function mainFeatures()
    {
        return view('pages.mainFeatures');
    }
}
