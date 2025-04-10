<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;


class TaskController extends Controller
{

    public function index()
    {
        $teamforform = Team::all();

        $user = Team::whereHas('userteam', function (Builder $query) {
            $query->where('users.id', Auth::user()->id);
        })->with('userteam')->get();

        $logged_user = $user->pluck('id')->toArray();

        if (count($user) <= 0) {
            abort(403, 'Currently no employess are assigned to any team');
        }

        $tasks = Task::with(['user.userdetail', 'client'])->whereBelongsTo($user)->where('status', 'incomplete')->withCount(['taskmilestones', 'taskmilestones as completedMilestones' => function ($query) {
            $query->where('status', 'complete');
        }])->get();
        // dd($tasks);
        foreach ($tasks as $task) {
            $totalMilestones = $task->taskmilestones_count;
            $completedMilestones = $task->completedMilestones;

            if ($totalMilestones === 0) {
                $task->progress = 0;
            } else {
                $task->progress = ($completedMilestones / $totalMilestones) * 100;
            }
        }

        $totaltasks = Task::whereIn('team_id', $logged_user)->whereNot('status', 'cancelled')->count();
        $completedtasks = Task::whereIn('team_id', $logged_user)->where('status', 'completed')->count();
        $activetasks = Task::whereIn('team_id', $logged_user)->where('status', 'incomplete')->count();
        $todaysdeadline = Task::whereIn('team_id', $logged_user)->where('status', 'incomplete')
            ->whereRaw('CAST(deadline_date AS DATE) = ?', [Carbon::today()->toDateString()])
            ->count();

        return response()->json(['data' => [$tasks, $totaltasks, $completedtasks, $activetasks, $todaysdeadline, $teamforform]], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $teams = Team::withWhereHas('userteam', function ($query) {
            $query->whereHas('roles', function (Builder $roleQuery) {
                $roleQuery->where('name', 'team-member');
            });
        })->get();


        $clients = Client::all();

        return response()->json(['data' => [$teams, $clients]], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'task_name' => 'required|string|max:255',
            'task_description' => 'required|string|max:300000',
            'task_file' => 'nullable',
            'team_id' => 'required|exists:teams,id',
            'account' => 'required|in:financial,non-financial',
            'client_id' => 'nullable|exists:clients,id',
            'estimated_budjet' => 'nullable|numeric',
            'word_count' => 'nullable|numeric',
            'starting_date' => 'required|date|after_or_equal:today',
            'deadline_date' => 'required|date|after_or_equal:starting_date',
        ]);

        if ($request->hasFile('task_file')) {
            $filePath = uploadFile($request, 'task_file');
        } else {
            $filePath = null;
        }

        DB::beginTransaction();
        try {
            $user = User::findOrFail(Auth::id());
            $task = $user->task()->create([
                'task_name' => $request->task_name,
                'task_description' => $request->task_description,
                'task_file' => $filePath,
                'team_id' => $request->team_id,
                'status' => 'incomplete',
                'account' => $request->account,
                'client_id' => $request->client_id,
                'word_count' => $request->word_count,
                'estimated_budjet' => $request->estimated_budjet,
                'starting_date' => $request->starting_date,
                'deadline_date' => $request->deadline_date,
            ]);

            $task->update([
                'task_num' => 'TN-00' . $task->id,
            ]);

            DB::commit();
            return response()->json(['data' => 'Task has been Created successfully'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['data' => 'Oppsss ! something went wrong'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $task = Task::with([
            'taskmilestones.assignedTo',
            'taskmilestones.assignedFrom',
            'taskmilestones.actionplan',
        ])
            ->withCount([
                'taskmilestones',
            ])
            ->withSum('taskmilestones', 'word_count')
            ->find($id);
        foreach ($task->taskmilestones as $taskmilestone) {
            $taskmilestone->tdl_count = 0;
            $i = 0;
            foreach ($taskmilestone->actionplan as $actionplan) {
                $i++;
            }
            $taskmilestone->tdl_count = $i;
        }

        return response()->json(['data' => $task], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $task = Task::find($id);
        $users = User::with('userdetail')->where('id', '!=', Auth::user()->id)->get();
        $teams = Team::whereNotNull('company_id')->get();


        return response()->json(['data' => [$task, $users, $teams]], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'task_name' => 'required|string|max:255',
            'task_description' => 'required|string|max:300000',
            'task_file' => 'nullable|file',
            'team_id' => 'required|exists:teams,id',
            'account' => 'required|in:financial,non-financial',
            'client_id' => 'nullable|exists:mysqlsecond.clients,id',
            'estimated_budjet' => 'nullable|numeric',
            'word_count' => 'nullable|numeric',
            'starting_date' => 'required|date|after_or_equal:today',
            'deadline_date' => 'required|date|after_or_equal:starting_date',
        ]);

        $task = Task::findOrFail($id);

        if ($task->created_by != Auth::user()->id) {
            abort(403);
        }

        if ($request->hasFile('task_file')) {
            $filePath = uploadFile($request, 'task_file');
        } else {
            $filePath = $task->task_file;
        }

        DB::beginTransaction();
        try {
            $task->update([
                'task_name' => $request->task_name,
                'task_description' => $request->task_description,
                'task_file' => $filePath,
                'team_id' => $request->team_id,
                'account' => $request->account,
                'client_id' => $request->client_id,
                'word_count' => $request->word_count,
                'estimated_budjet' => $request->estimated_budjet,
                'starting_date' => $request->starting_date,
                'deadline_date' => $request->deadline_date,
            ]);

            DB::commit();
            return response()->json(['data' => 'Task has been updated successfully'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['data' => 'Oppsss ! something went wrong'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Find the task by ID
        DB::beginTransaction();
        try {
            $task = Task::findOrFail($id);
            if ($task->created_by != Auth::user()->id) {
                abort(403);
            }
            $taskdelete = $task->update([
                'status' => 'cancelled',
            ]);
            $task->taskmilestones()->delete();
            DB::commit();
            return response()->json(['data' => 'Task has been deleted successfully'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['data' => 'Oops. Something went wrong'], 500);
        }
    }


    public function markasread(string $id)
    {
        $user = User::with('userteam')->find(Auth::user()->id);
        $task = Task::find($id);
        $isPartOfAssignedTeam = false;

        foreach ($user->userteam as $userteam) {
            if ($task->team_id == $userteam->id) {
                $isPartOfAssignedTeam = true;
                break; // Exit the loop as soon as we find a matching team
            }
        }

        if (!$isPartOfAssignedTeam) {
            abort(403, 'You are not part of the assigned team');
        }

        DB::beginTransaction();
        try {
            // Add your logic here
            $task->update([
                'completed_date' => Carbon::now(),
            ]);
            DB::commit();
            return response()->json(['data' => 'Task has been completed successfully'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['data' => 'Oops. Something went wrong'], 500);
        }
    }

    public function alltasks(Request $request)
    {
        $user = Team::whereHas('userteam', function (Builder $query) {
            $query->where('users.id', Auth::user()->id);
        })->with('userteam')->get();

        $logged_user = $user->pluck('id')->toArray();

        if (count($user) <= 0) {
            abort(403, 'Currently no employees are assigned to any team');
        }

        $tasks = Task::with(['user', 'client', 'team'])
            ->whereBelongsTo($user);

        // Apply search filter if there is input
        if ($request->input('search')) {
            $tasks->where(function ($query) use ($request) {
                $query->where('task_num', 'like', '%' . $request->input('search') . '%')
                    ->orWhere('task_name', 'like', '%' . $request->input('search') . '%');
            });
        }

        // Apply date range filter based on the dropdown filter
        if ($request->input('filter')) {
            $filter = $request->input('filter');
            switch ($filter) {
                case 'last_day':
                    $tasks->where('created_at', '>=', now()->subDay());
                    break;
                case 'last_7_days':
                    $tasks->where('created_at', '>=', now()->subDays(7));
                    break;
                case 'last_30_days':
                    $tasks->where('created_at', '>=', now()->subDays(30));
                    break;
                case 'last_month':
                    $tasks->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()]);
                    break;
                case 'last_year':
                    $tasks->whereYear('created_at', now()->year - 1);
                    break;
                default:
                    // No filter or invalid filter, no date filtering applied
                    break;
            }
        }

        // Retrieve the results
        $tasks = $tasks->get();

        // Calculate counts
        $totaltasks = $tasks->count();
        $completedtasks = $tasks->where('status', 'completed')->count();
        $cancelledtasks = $tasks->where('status', 'cancelled')->count();
        $activetasks = $tasks->where('status', 'incomplete')->count();


        return response()->json(['data' => [$tasks, $totaltasks, $completedtasks, $cancelledtasks, $activetasks]], 200);
    }

    public function createusers()

    {
        $users = [
            // provided by sir ahsan
            // ['name' => 'ahsan irfan patner', 'email' => 'ahsunirfan@gmail.com', 'password' => 'ahsunirfan123'], // super admin
            // ['name' => 'ahsan irfan team captain', 'email' => 'ahsunirfan1@gmail.com', 'password' => 'ahsunirfan1123'], // team captain
            // ['name' => 'Ariba', 'email' => 'ahsunirfan2@gmail.com', 'password' => 'ahsunirfan2123'], // Ariba BDM SEALS
            // ['name' => 'Aimon', 'email' => 'aimon.fiesta@gmail.com', 'password' => 'aimon.fiesta123'], //Aimon Team Lead Seals
            // ['name' => 'Zain', 'email' => 'zain.fiesta@gmail.com', 'password' => 'zain.fiesta123'], //BDM Scholars
            // ['name' => 'Uffaqnaz', 'email' => 'uffaqnaz.fiesta@gmail.com', 'password' => 'uffaqnaz.fiesta123'], //TL Scholars
            // ['name' => 'Maria', 'email' => 'maria12.fiesta@gmail.com', 'password' => 'maria12.fiesta123'], // Writer
            // ['name' => 'Sehar', 'email' => 'Seharfiesta@gmail.com', 'password' => 'Seharfiesta123'], // Writer
            // ['name' => 'Misbah', 'email' => 'misbahfiesta@gmail.com', 'password' => 'misbahfiesta123'], // TL Batman
            // ['name' => 'Jiya', 'email' => 'jiyafiesta59@gmail.com', 'password' => 'jiyafiesta59123'], // writer
            // ['name' => 'Humma ', 'email' => 'humafiesta@gmail.com', 'password' => 'humafiesta123'], // BDM
            // ['name' => 'Iqra ', 'email' => 'iqranadeem.fiesta@gmail.com', 'password' => 'iqranadeem.fiesta123'], // Business Development Manager
            // ['name' => 'mehwish', 'email' => 'mehwish.fiesta5811@gmail.com', 'password' => 'mehwish.fiesta5811123'], // writer


            // provided by mam ramsha
            // ['name' => 'Ramsha Fatima Saeed', 'email' => 'Coordinatorfcs204@gmail.com', 'password' => 'Coordinatorfcs204123'], // Coordinator
            // ['name' => 'Maha Yaqub', 'email' => 'maha.fiesta0908@gmail.com', 'password' => 'maha.fiesta0908123'], // marvel Team Leader
            // ['name' => 'Azra Tayyab', 'email' => 'azrafiesta1@gmail.com', 'password' => 'azrafiesta1123'], // Warriors Team Leader
            // ['name' => 'Mameeza Saeed', 'email' => 'teamleader.fhm@gmail.com', 'password' => 'teamleader.fhm123'], // Sharks Team Leader
            // ['name' => 'Mehreen Siddiquie', 'email' => 'mehreensiddiquie.fiesta@gmail.com', 'password' => 'mehreensiddiquie.fiesta123'], //Spiderman Team Leader
            // ['name' => 'Rameen Zahra', 'email' => 'rameenzahra.fiesta@gmail.com', 'password' => 'rameenzahra.fiesta123'], //Gladiators Team Leader
            // ['name' => 'Arifa Naseem', 'email' => 'arifa.fiesta448@gmail.com', 'password' => 'arifa.fiesta448123'], // Marvels Trail Team Leader

        ];

        foreach ($users as $user) {
            User::create($user);
        }

        $roles = ['super-admin', 'team-captain', 'bussiness-development-manager', 'human-resources', 'team-member', 'team-leader'];
        foreach ($roles as $role) {
            Role::create(['name' => $role]);
        }

        $assignrole = User::first();
        $superuser = $assignrole->assignrole(['team-captain', 'super-admin']);
        dd($assignrole);
    }
}
