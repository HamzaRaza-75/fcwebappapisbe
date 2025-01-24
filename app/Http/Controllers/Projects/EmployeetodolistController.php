<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\Actionplan;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\Task;
use App\Models\TaskMilestone;
use App\Models\TaskRevision;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class EmployeetodolistController extends Controller
{

    public function taskindex(string $id)
    {
        $employesstdls = TaskMilestone::with('actionplan.submittedBy', 'task')->withSum(['actionplan' => function ($query) {
            $query->where('revision', 0);
        }], 'word_count')->findOrFail($id);

        // dd($employesstdls);
        return view('task.viewtdl', compact('employesstdls'));
    }


    public function markascomplete(string $id)
    {
        TaskMilestone::findOrFail($id)->update([
            'status' => 'complete',
        ]);
        return redirect()->back();
    }

    public function reassigntask(Actionplan $todolist, Request $request)
    {
        // dd($request->all());

        $todolist->load('taskmilestones');

        $validation = $request->validate([
            'revision_title' => 'required|string|max:255',
            'revision_description' => 'nullable|string',
            'revision_file' => 'nullable|file|max:10000',
            'deadline_date' => 'required|date',
        ]);

        $revisionfile = uploadFile($request, 'revision_file');

        DB::beginTransaction();
        try {
            $todolist->update([
                'revision' => true,
            ]);

            TaskMilestone::find($todolist->taskmilestones->id)->update([
                'status' => 'incomplete'
            ]);

            TaskRevision::create([
                'action_plan_id' => $todolist->id,
                'revision_title' => $request->revision_title,
                'revision_description' => $request->revision_description,
                'revision_file' => $revisionfile,
                'start_working_at' => $request->deadline_date,
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }


        return redirect()->back();
    }

    public function seenreasing($id)
    {
        $revision = TaskRevision::findorFail($id);

        $revision->update([
            'seen_at' => Carbon::now(),
        ]);

        return redirect()->back();
    }



    public function taskstore(Request $request, $id)
    {
        $request->validate([
            // validation rules here
        ]);

        $taskTodolist = Task::findOrFail($id);
        // update taskTodolist properties
        $taskTodolist->actionplan()->create([
            'submited_to' => 1,
            'submited_by' => 2,
            'task_file' => null,
            'task_starting_date' => Carbon::now(),
            'task_submition_date' => Carbon::now(),
        ]);

        dd('task todo list added successfully');
    }


    public function todolistupdate(Request $request, $id)
    {
        $validate = $request->validate([
            'word_count' => 'nullable|integer',
            'worth' => 'required|integer',
            'task_file' => 'nullable|file|max:5000',
            'action_plan_starting_datetime' => 'required|date',
        ]);

        DB::beginTransaction();

        try {

            $actionplan = Actionplan::findOrFail($id);

            if ($request->hasFile('task_file')) {
                $filePath = uploadFile($request, 'task_file');
            } else {
                $filePath = $actionplan->task_file;
            }

            if (Auth::user()->id != $actionplan->submited_by) {
                abort(403, 'You are not the user who create the action plan');
            }

            $actionplan->update([
                'word_count' => $request->word_count,
                'worth' => $request->worth,
                'task_file' => $filePath,
                'action_plan_starting_datetime' => $request->action_plan_starting_datetime,
                'action_plan_submition_datetime' => Carbon::now(),
            ]);

            DB::commit();
        } catch (\Exception $e) {

            DB::rollBack();
        }

        return redirect()->back();
    }



    public function tdlindex()
    {

        $revisions = TaskRevision::whereHas('actionplan', function ($query) {
            $query->where('submited_by', Auth::user()->id);
        })
            ->whereHas('actionplan.taskmilestones', function ($quer) {
                $quer->where('status', 'incomplete');
            })
            ->with([
                'actionplan' => ['taskmilestones' => ['assignedFrom']]
            ])
            ->get();

        // dd($revisions);
        //  dd($revisions);

        $todolists = Actionplan::withWhereHas('taskmilestones', function ($query) {
            $query->where('status', 'incomplete');
        })
            ->where('submited_by', Auth::user()->id)
            ->where('revision', false)
            ->get();

        return view('employess.tdls.reassigntdl', compact('revisions', 'todolists'));
    }
}
