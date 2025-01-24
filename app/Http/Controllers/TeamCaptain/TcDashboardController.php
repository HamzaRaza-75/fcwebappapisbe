<?php

namespace App\Http\Controllers\TeamCaptain;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\Task;
use App\Models\TaskMilestone;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class TcDashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $logged_user = User::with('userteam.userteam')->find(Auth::id());
        $total_users = $logged_user->userteam->reduce(function ($carry, $team) {
            return $carry + $team->userteam->count();
        });

        $logged_team = $logged_user->userteam->pluck('id')->toArray();

        $teamcount = $logged_user->userteam()->count();

        $active_tasks = Task::whereIn('team_id' , $logged_team)->with('client' , 'user' , 'team')->where('status' , 'incomplete')->get();

        $tasks = $active_tasks->count();

        return view('teamcaptain.tcdashboard' , compact('teamcount' , 'total_users' , 'tasks' , 'active_tasks' , 'logged_user'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function dashboard()
    {
        dd(Auth::user());
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
       $teams = Team::with('teamposition')->where('id' , $id)->get();
       $roles = Role::all();
       return view('dashboard' , compact('teams' , 'roles'));
    }


    public function userteamrequest(Request $request)
    {
        dd($request->all());
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
