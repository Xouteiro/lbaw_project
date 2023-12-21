<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    public function store(Request $request)
    {
        if(Auth::check()){
            $user = User::findOrFail(Auth::user()->id);
            if($user->blocked){
                return redirect()->route('home');
            }
        }
        $name = $request->input('name');
        $address = $request->input('address');


        $location = new Location();
        $location->name = $name;
        $location->address = $address;
        $location->save();

        $locationID = Location::where('name', $name)->where('address', $address)->first()->id;

        return response()->json(['id' => $locationID ,'message' => 'Location created successfully'], 200);
    }

    public function delete(Request $request)
    {
        if(Auth::check()){
            $user = User::findOrFail(Auth::user()->id);
            if($user->blocked){
                return redirect()->route('home');
            }
        }
        $locationID = $request->input('id_location');
        
        if($locationID == 79){
            return response()->json(['message' => 'Cannot delete that location'], 403);
        }
        $allEventstoChange = Event::where('id_location', $locationID)->get();
        foreach($allEventstoChange as $event){
            $event->id_location = 79;
            $event->save();
        }

        $location = Location::find($locationID);

        $location->delete();

        return response()->json(['message' => 'Location deleted successfully'], 200);
    }
}
