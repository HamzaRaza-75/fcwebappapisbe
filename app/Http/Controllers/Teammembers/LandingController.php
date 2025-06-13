<?php

namespace App\Http\Controllers\Teammembers;

use App\Http\Controllers\Controller;
use App\Models\TaskMilestone;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $taskmilestone = TaskMilestone::assigneduser()->with('assignedFrom.userdetail', 'assignedTo.userdetail', 'task.user')
            ->where('status', 'incomplete')
            ->where('seen_at', null)
            ->get();
        return response()->json(['data' => [$taskmilestone]], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
