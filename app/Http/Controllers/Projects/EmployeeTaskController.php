<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\TaskMilestone;
use App\Models\User;
use App\Notifications\EmployeeTaskSubmitted;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeTaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $taskmilestone = TaskMilestone::assigneduser()->with('assignedTo', 'assignedFrom', 'task.user')
            ->where('status', 'incomplete')
            ->get();

        // dd($taskmilestone);
        return view('employess.tasks.index', compact('taskmilestone'));
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(TaskMilestone $taskmilestone, Request $request)
    {
        // Validate the request
        $validate = $request->validate([
            'word_count' => 'nullable|integer',
            'worth' => 'required|integer',
            'task_file' => 'nullable|file|max:5000',
            'action_plan_starting_datetime' => 'required|date',
        ]);

        DB::beginTransaction();

        try {
            // Upload the file if it exists
            $filePath = uploadFile($request, 'task_file');

            // Create a new action plan for the task milestone
            $taskmilestone->actionplan()->create([
                'submited_by' => Auth::user()->id,
                'word_count' => $request->word_count,
                'worth' => $request->worth,
                'task_file' => $filePath,
                'action_plan_starting_datetime' => $request->action_plan_starting_datetime,
                'action_plan_submition_datetime' => Carbon::now(),
            ]);

            $user = User::find($taskmilestone->assigned_by);
            $user->notify(new EmployeeTaskSubmitted($taskmilestone));

            DB::commit();
            notify()->success('Your Action Plan Has been created sucessfully');
        } catch (\Exception $e) {
            DB::rollBack();
            notify()->error('Oppps . Something went wrong');
        }
        return redirect()->back();
    }
    /**
     * Display the specified resource.
     */
    public function show(TaskMilestone $taskmilestone)
    {
        $taskmilestone->loadCount('actionplan');
        if ($taskmilestone->assigned_to == Auth::user()->id) {
            if ($taskmilestone->seen_at == null) {
                // dd('entering in the second funciton');
                $taskmilestone->update([
                    'seen_at' => Carbon::now(),
                ]);
            }
        } else {
            abort(403, 'Only the employee which have assigned the task can see this page');
        }
        // dd($taskmilestone);

        return view('employess.tasks.view', compact('taskmilestone'));
    }

    /**
     * Show the form for editing the specified resource.
     */


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
