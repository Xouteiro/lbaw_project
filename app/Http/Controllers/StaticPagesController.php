<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class StaticPagesController extends Controller
{
    public function home()
    {
        if (Auth::check()) {
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
