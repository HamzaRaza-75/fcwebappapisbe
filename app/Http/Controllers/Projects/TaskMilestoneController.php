<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskMilestone;
use App\Models\Team;
use App\Models\User;
use App\Notifications\EmployeeTaskAssigned;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class TaskMilestoneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Task $task)
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Task $task)
    {
        $task->loadSum('taskmilestones', 'word_count');

        $users = User::whereHas('roles', function ($query) {
            $query->where('name', 'team-member');
        })->get();

        // Fetch the team and its associated users who are not team-members
        $team = Team::with(['userteam' => function ($query) {
            $query->whereDoesntHave('roles', function ($roleQuery) {
                $roleQuery->where('name', 'team-member');
            });
        }])->find($task->team_id);

        // Check if the team has any users
        if ($team->userteam->isEmpty()) {
            abort(403, 'No users in the team.');
        }

        // Check if the authenticated user is in the team
        $userInTeam = $team->userteam->contains(function ($user) {
            return $user->id == Auth::user()->id;
        });

        if (!$userInTeam) {
            return response()->json(['data' => 'You are unautherize to do this'], 403);
        }
        return response()->json(['data' => [$task, $users]], 200);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Task $task, Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'task_milestone_name.*' => 'required|string|max:255',
            'task_milestone_description.*' => 'required|string',
            'worth.*' => 'required|max:255',
            'assigned_to.*' => 'required',
            'word_count.*' => 'nullable|numeric',
            'deadline_date.*' => 'required|date|after:today',
            'task_milestone_file.*' => 'nullable|file',
        ]);

        // Iterate over the form data to create schedule entries
        $team = Team::with(['userteam' => function ($query) {
            $query->whereDoesntHave('roles', function ($roleQuery) {
                $roleQuery->where('name', 'team-member');
            });
        }])->find($task->team_id);

        if ($team->userteam->isEmpty()) {
            // Handle the case where the team does not have any users
            abort(403, 'No users in the team.');
        }
        // Check if the authenticated user is in the team
        $userInTeam = $team->userteam->contains(function ($user) {
            return $user->id == Auth::user()->id;
        });

        if (!$userInTeam) {
            abort(403, 'Unauthorized action. You are not that user from the team to which the task is assigned.');
        }
        DB::beginTransaction();
        try {
            foreach ($validated['task_milestone_name'] as $index => $name) {
                $taskmilestone = new TaskMilestone();
                $taskmilestone->task_milestone_name = $name;
                $taskmilestone->word_count = $validated['word_count'][$index] ?? null;
                $taskmilestone->worth = $validated['worth'][$index];
                $taskmilestone->task_id = $task->id;
                $taskmilestone->assigned_by = Auth::user()->id;
                $taskmilestone->assigned_to = $validated['assigned_to'][$index];
                $taskmilestone->status = 'incomplete';
                $taskmilestone->deadline_date = $validated['deadline_date'][$index];
                $taskmilestone->task_milestone_description = $validated['task_milestone_description'][$index];

                if (isset($validated['task_milestone_file'][$index])) {
                    $filePath = uploadFile($request, 'task_milestone_file', $index);
                    $taskmilestone->task_milestone_file = $filePath;
                }

                $taskmilestone->save();

                $assignedUser = User::find($validated['assigned_to'][$index]);
                $assignedUser->notify(new EmployeeTaskAssigned($taskmilestone));
            }
            DB::commit();
            return response()->json(['data' => 'Taskmilestone has been added successfully'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['data' => 'Oppsss ! something went wrong'], 500);
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task, TaskMilestone $milestone)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(String $id)
    {
        $taskmilestone = TaskMilestone::find($id);
        Gate::authorize('delete', $taskmilestone);

        DB::beginTransaction();
        try {
            // Add your logic here
            $taskmilestone->delete();
            DB::commit();
            return response()->json(['data' => 'Taskmilestone has been deleted successfully'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['data' => 'Oops. Something went wrong'], 500);
        }
    }
}
