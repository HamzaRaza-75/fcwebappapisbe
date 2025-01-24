<?php

namespace App\Http\Controllers\Shedule;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Shedule;

class SheduleController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index() : View
    {
        $shedules = Shedule::usershedule()->orderBy('end_time')->get();
        $shedulecount = $shedules->count();

        return view('employess.shedule', compact('shedules'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'shedule_name' => 'required|string|max:255',
            'shedule_description' => 'nullable|string',
            'end_time' => 'required|date|after_or_equal:today',
        ]);

        Auth::user()->shedules()->create($request->all());

        return redirect()->route('schedules.index')->with('success', 'Task scheduled successfully.');
    }

    public function edit(Shedule $shedules)
    {
        if ($shedules->user_id != Auth::id()) {
            abort(403);
        }

        return view('schedules.edit', compact('schedule'));
    }

    public function update(Request $request, Shedule $shedules)
    {
        if ($shedules->user_id != Auth::id()) {
            abort(403);
        }

        $request->validate([
            'shedule_name' => 'required|string|max:255',
            'shedule_description' => 'nullable|string',
            'end_time' => 'required|date|after_or_equal:today',
        ]);

        $shedules->shedules()->update($request->all());

        return redirect()->route('schedules.index')->with('success', 'Task updated successfully.');
    }

    public function destroy(Shedule $schedule)
    {
        if ($schedule->user_id != Auth::id()) {
            abort(403);
        }

        $schedule->delete();
        notify()->success('Your shedule has been deleted successfully' , 'Shedule Deleted');
        return redirect()->route('schedules.index');
    }
}
