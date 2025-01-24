<?php

namespace App\Http\Controllers\Superadmin;

use App\Charts\TeamProjectsChart;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\TeamPositions;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $teams = User::with(['userteam' =>
        ['company' , 'teamposition']
        ])->find(Auth::id());

        $userteams = $teams->userteam->pluck('id')->toArray();

        $teams = Team::whereIn('id' , $userteams)->with('company', 'teamposition')->withCount(['userteam' , 'teamposition'])->get();
        $team_count = $teams->count();
        $totalemployess = $teams->sum('userteam_count');
        $teampositions = $teams->sum('teamposition_count');
        $activetasks = Task::whereIn('id' , $userteams)->where('status','incomplete')->count();

        // dd($teams);
        return view('teamcaptain.team.index', compact('teams', 'team_count' , 'totalemployess' , 'teampositions' , 'activetasks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companies = Company::select('id', 'company_name')->get();

        if(count($companies) <= 0)
        {
            notify()->info('Please Add the company first' , 'Create Company');
            return to_route('company.index');
        }

        return view('teamcaptain.team.create', compact('companies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'team_name' => 'required|unique:companies,company_name|max:255',
            'description' => 'required|max:400',
            'team_image' => 'nullable|image|max:4048',
            'company_id' => 'required',
            'position_name.*' => 'required|max:100',
        ]);


        $team_image = uploadFile($request, 'team_image');


        DB::beginTransaction();
        try {

            // dd('Entering in the try block');
            $team = Team::create([
                'team_name' => trim($request->team_name),
                'slug' => Str::slug($request->team_name, '-'),
                'description' => trim($request->description),
                'team_image' => $team_image,
                'company_id' => $request->company_id,
            ]);
            foreach ($request->position_name as $position_name) {
                TeamPositions::create([
                    'position_name' => $position_name,
                    'team_id' => $team->id,
                ]);
            }

            $user = User::find(Auth::id());
            $user->userteam()->attach($team);

            DB::commit();
            notify()->success('Company has been added successfully ⚡️');
        } catch (Exception $e) {

            DB::rollBack();
            notify()->error('Something went wrong.... Please try again later' . $e->getMessage());
        }

        return to_route('team.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(TeamProjectsChart $chart, string $id)
    {
        $team = Team::with([
            'userteam' => function ($query) {
                $query->whereHas('roles', function (Builder $query) {
                    $query->where('name', 'team-member');
                })
                    ->with('roles');
            },
            'company',
            'tasks' => function ($query) {
                $query->where('status', 'incomplete'); // Use constant for status
            },
            'tasks.user.userteam' // Correctly include the user relationship within tasks
        ])
            ->withCount([
                'tasks',
                'userteam',
                'tasks as activetasks' => function ($query) {
                    $query->where('status', 'incomplete'); // Use constant for status
                },
                'tasks as cancelledtasks' => function ($query) {
                    $query->where('status', 'cancelled'); // Use constant for status
                },
                'tasks as completedtasks' => function ($query) {
                    $query->where('status', 'completed'); // Use constant for status
                },
            ])
            ->find($id);

        // cross team configration
        foreach ($team->tasks as $teamtask) {
            $teamtask->crossteam = 'true';
            foreach ($teamtask->user->userteam as $userteam) {
                if ($userteam->id == $team->id) {
                    $teamtask->crossteam = 'false';
                }
            }
        }

        return view('teamcaptain.team.view', compact('team'), ['chart' => $chart->build([$team->activetasks, $team->cancelledtasks, $team->completedtasks], $team->team_name)]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $companies = Company::select('id', 'company_name')->get();

        $team = Team::with('teamposition')->find($id);

        return view('teamcaptain.team.edit', compact('team', 'companies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'team_name' => 'required|max:255',
            'description' => 'required|max:400',
            'team_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'company_id' => 'required',
        ]);

        $team = Team::findOrFail($id);

        if ($request->hasFile('team_image')) {
            $team_image = uploadFile($request, 'team_image');
        } else {
            $team_image = $team->team_image;
        }

        $team->update([
            'team_name' => trim($request->team_name),
            'slug' => Str::slug($request->team_name, '-'),
            'description' => trim($request->description),
            'team_image' => $team_image,
            'company_id' => $request->company_id,
        ]);

        notify()->success('Team has been updated successfully ⚡️');
        return to_route('team.index');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Team::find($id)->delete();
        notify()->success('Team has been deleted successfully', 'Team Deleted Successfully');
        return redirect()->back();
    }

    public function positioncreate(Team $team, Request $request)
    {
        // $position->delete();
        $validation = $request->validate([
            'position_name' => 'required',
        ]);

        $team->teamposition()->create($request->all());
        notify()->success('New Team position has been inserted successfully', 'Team position Inserted');
        return redirect()->back();
    }


    public function positiondestroy(TeamPositions $position)
    {
        $position->delete();
        notify()->success('Team position has been deleted successfully', 'Team position Deleted');
        return redirect()->back();
    }
}
