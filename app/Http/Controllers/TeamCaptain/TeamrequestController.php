<?php

namespace App\Http\Controllers\TeamCaptain;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Team;
use App\Models\TeamRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Spatie\Permission\Models\Role;

class TeamrequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // return Task::addSelect([
        //     'assigned_by' => User::select('name' )
        //         ->whereColumn('assigned_to', 'users.id')
        //         ->orderByDesc('id')
        // ])->get();



        // $teams = Task::get();
        // $teams = $teams->reject(function(Task $team) {
        //     $team->rejected;
        // });
        // dd($teams);

        // $teamrequests = TeamRequest::with('user' , 'teamposition' , 'role' , 'team')->get();
        // return response()->json($teamrequests);
        // return view('layouts.teamcaptain', compact('teamrequests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(string $id)
    {
        try {
            $teamRequest = TeamRequest::findOrFail($id);
            $user = User::findOrFail($teamRequest->user_id);
            $role = Role::findById($teamRequest->role_id);

            $teamId = $teamRequest->team_id;
            $teamPositionId = $teamRequest->teamposition_id;

            DB::beginTransaction();

            // Assigning the user to the team
            $user->userteam()->sync([$teamId]);

            // Assigning the role to the user
            $roleName = $role->name;
            $user->syncRoles([$roleName]);

            // Updating team position if it exists
            if (!is_null($teamPositionId)) {
                $user->update([
                    'teamposition_id' => $teamPositionId,
                ]);
            }

            // Updating the team request status
            $teamRequest->update([
                'request_status' => 'accepted',
            ]);

            DB::commit();
            return response()->json(['data' => 'User has been accepted Successfully'], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['data' => 'Opps ! some error occur while accepting user'], 500);
        }
    }

    public function rejectteamrequest(string $id)
    {
        DB::beginTransaction();
        try {
            // Add your logic here
            $teamrequest = TeamRequest::find($id)->update([
                'request_status' => 'rejected'
            ]);
            DB::commit();
            return response()->json(['data' => 'Team Request has been rejected successfully'], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['data' => 'Oops. some error occur while accepting team request'], 500);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
