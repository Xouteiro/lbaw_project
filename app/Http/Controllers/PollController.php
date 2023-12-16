<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Models\Event;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PollController extends Controller
{

    public function store(Request $request)
    {   
        
        $options = json_decode($request->input('options'));
        $request->validate([
            'title' => 'required|string|max:255',
            'eventId' => 'required|numeric',
        ]);

        Poll::create([
            'title' => $request->input('title'),
            'id_event' => $request->input('eventId'),
            'id_user' => Auth::user()->id,
        ]);

        foreach ($options as $option) {
            //need vaalidation
            //nao permitir criar opções com o mesmo nome no mesmo poll
            //nao permitir criar mais que x polls
            Option::create([
                'id_poll' => Poll::where('id_event', $request->input('eventId'))->first()->id,
                'name' => $option,
            ]);
        }

        return response()->json(['message' => 'Poll creation successful'], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Poll $poll)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Poll $poll)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Poll $poll)
    {
        //
    }
}
