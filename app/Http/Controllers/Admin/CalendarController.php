<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CalendarEvent;

class CalendarController extends Controller
{
    public function index()
    {
        return view('admin.calendar');
    }

    public function getEvents()
    {
        return response()->json(CalendarEvent::all());
    }

    public function store(Request $request)
    {
        $event = CalendarEvent::create($request->only('title', 'start', 'end'));
        return response()->json($event);
    }

    public function update(Request $request, $id)
    {
        $event = CalendarEvent::findOrFail($id);
        $event->update($request->only('title', 'start', 'end'));
        return response()->json(['message' => 'Event updated']);
    }

    public function destroy($id)
    {
        CalendarEvent::findOrFail($id)->delete();
        return response()->json(['message' => 'Event deleted']);
    }
}
