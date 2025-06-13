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

        return response()->json(['data' => [$employesstdls]], 200);
    }


    public function markascomplete(string $id)
    {
        DB::beginTransaction();
        try {
            // Add your logic here
            TaskMilestone::findOrFail($id)->update([
                'status' => 'complete',
            ]);
            DB::commit();
            return response()->json(['data' => 'Task has been completed successfully'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['data' => 'Oops. Something went wrong'], 500);
        }
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
            return response()->json(['data' => 'Task has been reassigned successfully'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['data' => 'Oppsss ! something went wrong'], 500);
        }
    }

    public function seenreasing($id)
    {
        DB::beginTransaction();
        try {
            // Add your logic here
            $revision = TaskRevision::findorFail($id);
            $revision->update([
                'seen_at' => Carbon::now(),
            ]);
            DB::commit();
            return response()->json(['data' => 'Revision has been seen'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['data' => 'Oops. Something went wrong'], 500);
        }
    }



    // *
    // **
    // ***
    // iss waly function mein koi error hy isko dekh lena

    public function taskstore(Request $request, $id)
    {
        $request->validate([
            // validation rules here
        ]);

        DB::beginTransaction();
        try {
            // Add your logic here
            $taskTodolist = Task::findOrFail($id);
            $taskTodolist->actionplan()->create([
                'submited_to' => 1,
                'submited_by' => 2,
                'task_file' => null,
                'task_starting_date' => Carbon::now(),
                'task_submition_date' => Carbon::now(),
            ]);

            DB::commit();
            return response()->json(['data' => '/n has been ( ) successfully'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['data' => 'Oops. Something went wrong'], 500);
        }
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
            return response()->json(['data' => 'Todo list has been saved successfully'], 201);
        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json(['data' => 'Oppsss ! something went wrong'], 500);
        }
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

        return response()->json(['data' => [$revisions, $todolists]], 200);
        return view('employess.tdls.reassigntdl', compact('revisions', 'todolists'));
    }
}
