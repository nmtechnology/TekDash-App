<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CalendarController extends Controller
{
    //
    public function showEvents(Request $request) {
        $event = Event::get(['title', 'start', 'end']);
        return response()->json(["events" => $event]);
      }
}
