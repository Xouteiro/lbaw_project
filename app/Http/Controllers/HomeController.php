<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Support\Facades\Auth;


class HomeController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            $events = Event::where('hide_owner', '=', false)->inRandomOrder()->get()->take(6);
            return view('pages.home', ['events' => $events]);
        } else {
            $events = Event::where([['hide_owner', '=', false] , ['public', '=', true]] )->inRandomOrder()->get()->take(6);
            return view('pages.home', ['events' => $events]);
        }
    }
}