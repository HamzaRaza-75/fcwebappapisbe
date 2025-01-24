<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Mail\RoleEmail;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\Task;
use App\Models\TaskMilestone;
use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ReportingController extends Controller
{
    public function employeeactionplanreport(Request $request): View
    {
        // Get all teams that have tasks or projects associated with them
        $teams = Team::whereHas('tasks')->get();

        // Initialize the $mainquery variable
        $tasks = null;
        $totalcount = null;
        $totalwordcount = null;
        $totalhours = null;


        // Check if the request has any of the specified filters
        if ($request->hasAny(['start_date', 'end_date', 'first_company', 'second_company'])) {

            // Validate the input fields
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date|before_or_equal:today',
                'first_company' => 'required',
                'second_company' => 'required',
            ]);

            $secondteam = User::with(['userteam' => function ($query) use ($request) {
                $query->where('team_id', $request->second_company);
            }])->get();

            // Extract the user IDs from the second team
            $userIds = $secondteam->pluck('id')->toArray();

            $tasksQuery = TaskMilestone::whereIn('assigned_to', $userIds)
                ->where('status', 'complete')
                ->withWhereHas('task', function ($query) use ($request) {
                    $query->whereBetween('starting_date', [$request->start_date, $request->end_date])
                        ->where('team_id', $request->first_company);
                })->with(['assignedTo', 'assignedFrom']);

            // Get the list of projects (if needed)
            $tasks = $tasksQuery->get();

            $totalcount = $tasks->count();

            $totalhours = $tasksQuery->sum('worth');

            $totalwordcount = $tasksQuery->sum('word_count');
        }

        // Return the view with both 'teams' and 'mainquery' data
        return view('reports.crossteam', compact('teams', 'tasks', 'totalcount', 'totalwordcount', 'totalhours'));
    }


    public function projectwithtimelinereport(Request $request): View
    {
        // Initialize null results for both tasks and projects
        $resultA = null;

        if ($request->input('search')) {
            // Validate the search input
            $validated = $request->validate([
                'search' => 'required',
            ]);

            // Search in Task model
            $resultA = Task::where('task_num', 'like', '%' . $request->search . '%')
                ->orWhere('task_name', 'like', '%' . $request->search . '%')
                ->with(['taskmilestones' => ['assignedFrom', 'assignedTo'], 'actionplanon', 'user', 'client', 'team'])
                ->withCount('taskmilestones', 'actionplanon')
                ->withSum('taskmilestones', 'worth')
                ->withSum('actionplanon', 'worth')
                ->first();
            if ($resultA == null) {
                notify()->error('Record not found', '404');
            }
        }



        // Pass results to the view, even if both are null
        return view('reports.projecttimeline', [
            'tasks' => $resultA
        ]);
    }
}
