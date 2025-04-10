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
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $teams = User::with([
            'userteam' =>
            ['company', 'teamposition']
        ])->find(Auth::id());

        $userteams = $teams->userteam->pluck('id')->toArray();

        $teams = Team::whereIn('id', $userteams)->with('company', 'teamposition')->withCount(['userteam', 'teamposition'])->get();
        $team_count = $teams->count();
        $totalemployess = $teams->sum('userteam_count');
        $teampositions = $teams->sum('teamposition_count');
        $activetasks = Task::whereIn('id', $userteams)->where('status', 'incomplete')->count();

        $response = [
            $teams,
            $team_count,
            $totalemployess,
            $teampositions,
            $activetasks,
        ];
        return response()->json(['data' => $response], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companies = Company::select('id', 'company_name')->get();
        return response()->json(['data' => $companies], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'team_name' => 'required|max:255|unique:teams,team_name',
            'description' => 'required|max:400',
            'team_image' => 'nullable|image|max:4048',
            'company_id' => 'required',
            'position_name.*' => 'required|max:100',
        ]);

        if ($validation->fails()) {
            return response()->json(['data' => $validation->errors()], 422);
        }

        $validation->validate();

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
            return response()->json(['data' => 'Team has been created sucessfully'], 200);
        } catch (Exception $e) {

            DB::rollBack();
            return response()->json(['data' => 'Oppsss! something went wrong'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
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
            'tasks.user.userteam' // include the user relationship within tasks
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

        $response = [$team, $team->activetasks, $team->cancelledtasks, $team->completedtasks];
        return response()->json(['data' => $response], 200);
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

        $validation = Validator::make($request->all(), [
            'team_name' => ['required|max:255', Rule::unique('teams', 'team_name')->ignore($id)],
            'description' => 'required|max:400',
            'team_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'company_id' => 'required',
        ]);

        if ($validation->fails()) {
            return response()->json(['data' => $validation->errors()], 422);
        }

        $validation->validate();

        $team = Team::findOrFail($id);
        if ($request->hasFile('team_image')) {
            $team_image = uploadFile($request, 'team_image');
        } else {
            $team_image = $team->team_image;
        }

        DB::beginTransaction();
        try {
            // Add your logic here
            $team->update([
                'team_name' => trim($request->team_name),
                'slug' => Str::slug($request->team_name, '-'),
                'description' => trim($request->description),
                'team_image' => $team_image,
                'company_id' => $request->company_id,
            ]);
            DB::commit();
            return response()->json(['data' => 'Team has been updated successfully'], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['data' => 'Oops. Team is not updated successfully'], 500);
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            // Add your logic here
            Team::find($id)->delete();
            DB::commit();
            return response()->json(['data' => 'Data has been deleted successfully'], 201);
        } catch (Exception $exception) {
            DB::rollBack();
            return response()->json(['data' => 'Oops. Data is not deleted'], 500);
        }
    }

    public function positioncreate(Team $team, Request $request)
    {
        $validation = Validator::make($request->all(), [
            'position_name' => 'required',
        ]);

        if ($validation->fails()) {
            return response()->json(['data' => $validation->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // Add your logic here
            $team->teamposition()->create($request->all());
            DB::commit();
            return response()->json(['data' => 'Position has been added successfully'], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['data' => 'Oops. position  is not created'], 500);
        }
    }


    public function positiondestroy(TeamPositions $position)
    {
        DB::beginTransaction();
        try {
            // Add your logic here
            DB::commit();
            $position->delete();
            return response()->json(['data' => 'position has been deleted successfully'], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['data' => 'Oops. position  is not deleted'], status: 500);
        }
    }
}
