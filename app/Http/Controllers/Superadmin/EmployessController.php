<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Actionplan;
use App\Models\TaskMilestone;
use App\Models\Team;
use App\Models\User;
use ArielMejiaDev\LarapexCharts\LarapexChart;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Spatie\Permission\Models\Role as ModelsRole;

class EmployessController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $users = User::with('userteam')->find(Auth::user()->id);
        $logged_team = $users->userteam->pluck('id')->toArray();
        $logged_team_users = Team::whereIn('id', $logged_team)->with(['userteam'])->get();

        $alluser = $logged_team_users->sum(function ($team) {
            return $team->userteam->count(); // Sum up the count of users in each team
        });

        $blockedusers = $logged_team_users->sum(function ($team) {
            return $team->userteam->filter(function ($user) {
                return $user->status === 'blocked'; // Adjust the condition as needed
            })->count(); // Sum up the count of filtered users
        });

        $officebased = $logged_team_users->sum(function ($team) {
            return $team->userteam->filter(function ($user) {
                return $user->userdetail->working_domain === 'office'; // Adjust the condition as needed
            })->count(); // Sum up the count of filtered users
        });

        $assignmentbase = $logged_team_users->sum(function ($team) {
            return $team->userteam->filter(function ($user) {
                return $user->userdetail->working_domain === 'assignmentbase'; // Adjust the condition as needed
            })->count(); // Sum up the count of filtered users
        });

        $sepreate_users = $logged_team_users->sum(function ($team) {
            return $team->userteam->where('id', '!=', Auth::id())->count(); // Sum up the count of users in each team
        });

        return view('user.index', compact('logged_team_users', 'alluser', 'blockedusers', 'officebased', 'assignmentbase', 'sepreate_users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function viewFreeEmployess()
    {
        $teams = Team::whereHas('userteam', function ($query) {
            $query->whereHas('tasksAssignedTo', function ($query) {
                $query->where('status', '!=', 'incomplete');
            })
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'team-member');
                });
        })
            ->with(['userteam' => function ($query) {
                $query->whereHas('tasksAssignedTo', function ($query) {
                    $query->where('status', '!=', 'incomplete');
                })
                    ->whereHas('roles', function ($query) {
                        $query->where('name', 'team-member');
                    })
                    ->with('teamposition'); // Load teamposition for the filtered users
            }])
            ->get();

        return view('user.freeemployess', compact('teams'));
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
    public function show(Request $request, string $id): View
    {
        $chart = null;
        $chart2 = null;

        $roles = ModelsRole::all();
        $teams = Team::with('userteam')->get();
        $user = User::with(['roles', 'userteam', 'skills'])->find($id);

        if ($user->roles->contains('name', 'team-member')) {
            $user->loadCount('tasksAssignedTo');
            $user->load('latestTask');


            $monthlyMilestones = Actionplan::selectRaw("
            MONTH(action_plan_starting_datetime) as month,
            COUNT(*) as total,
            SUM(CASE WHEN revision = 1 THEN 1 ELSE 0 END) as revisions,
            SUM(CASE WHEN revision = 0 THEN 1 ELSE 0 END) as accepted
        ")
                ->where('submited_by', $id)
                ->whereYear('created_at', Carbon::now()->year)
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            $months = [];
            $approved = [];
            $revisions = [];

            foreach ($monthlyMilestones as $milestone) {
                $months[] = Carbon::create()->month($milestone->month)->format('F');
                $approved[] = $milestone->accepted;
                $revisions[] = $milestone->revisions;
            }



            // Word Count
            $chart = (new LarapexChart)->pieChart()
                ->setTitle('Total Word count')
                ->addData([
                    TaskMilestone::where('status', 'complete')->where('assigned_to', $id)->sum('word_count'),
                ])
                ->setLabels(['Total WordCount']);

            $chart2 = (new LarapexChart)->barChart()
                ->setTitle('Work Report.')
                ->setSubtitle('Employee Progress During the current year.' . Carbon::now()->year)
                ->addData('approved', $approved)
                ->addData('revisions', $revisions)
                ->setXAxis($months);
        }

        // dd($chart);


        return view('user.view', compact('roles', 'teams', 'user', 'chart', 'chart2'));
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
        // this is the module for creating the update users with allow him multiple team and multiple roles

        $user = User::find($id);
        $teamrequest = Team::findMany($request->teams);
        $user->userteam()->detach();
        foreach ($teamrequest as $teamreq) {
            $user->userteam()->attach([$teamreq->id]);
        }

        $roles = ModelsRole::findByName($request->roles);
        $user->syncRoles([$roles]);

        notify()->success('You have successfully updated the roles of users');
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);
        $user->delete();
    }

    public function blockUser(string $id)
    {
        $user = User::find($id)->update([
            'status' => 'blocked',
        ]);

        notify()->success('user has been blocked successfully', 'Block user');
        return redirect()->back();
    }

    public function unblockUser(string $id)
    {
        $user = User::find($id)->update([
            'status' => 'active',
        ]);

        notify()->success('user has been unblocked successfully', 'Unblock user');
        return redirect()->back();
    }


    public function deleteNotification()
    {
        $user = User::find(Auth::id());
        $user->notifications()->delete();
        notify()->success('All notification has been readed sucessfully');
        return redirect()->back();
    }
}
