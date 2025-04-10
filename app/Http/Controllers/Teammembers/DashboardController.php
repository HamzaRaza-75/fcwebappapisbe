<?php

namespace App\Http\Controllers\Teammembers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Team;
use App\Models\TeamRequest;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Notifications\UserRequestNotification;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        // $testingquery = ;
        // dd(User::with('courses')->toSql());

        $teams = Team::whereNotNull('company_id')->get();
        return response()->json(['data' => $teams], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    public function dashboard()
    {
        // dd(Auth::user());
        $roles = Auth::user()->roles;
        return response()->json(['data' => $roles], 200);
    }


    public function coursedashboard()
    {
        // dd(Auth::user());
        // $roles = Auth::user()->roles;
        // $bit = 0;
        // foreach ($roles as $role) {
        //     $bit = 1;
        // }

        // switch ($bit) {
        //     case '1':
        //         return to_route('course.index');
        //         break;

        //     default:
        //         notify()->error('You are not the member of our team', 'Not Allowed');
        //         return redirect()->intended('/');
        // }
    }


    public function userteamrequest(string $id, Request $request)
    {
        $userid = Auth::user()->id;
        $teamrequest = TeamRequest::where('user_id', $userid)->count();
        // dd($teamrequest);
        if ($teamrequest > 0) {
            return response()->json(['data' => 'You have already requested for the team'], 403);
        } else {
            $request->validate([
                'phone_no' => 'required|numeric',
                'gurdian_name' => 'required',
                'gurdian_phone_no' => 'required',
                'CNIC_image' => 'required',
                'dateofbirth' => 'required',
                'gender' => 'required',
                'profile_image' => 'required',
                'role_id' => 'required',
                'position_id' => 'nullable',
                'current_address' => 'nullable|max:255',
            ]);

            if ($request->hasFile('profile_image')) {
                $profile_image = time() . '.' . $request->profile_image->extension();
                $request->profile_image->move(public_path('images'), $profile_image);
            } else {
                $profile_image = null; // Set default value if no image uploaded
            }

            if ($request->hasFile('CNIC_image')) {
                $CNIC_image = time() . '.' . $request->CNIC_image->extension();
                $request->CNIC_image->move(public_path('images'), $CNIC_image);
            } else {
                $CNIC_image = null; // Set default value if no image uploaded
            }

            $user = User::find(Auth::user()->id);


            DB::beginTransaction();
            try {
                // Add your logic here
                $userdetail = $user->userdetail()->create([
                    'user_id' => Auth::user()->id,
                    'phone_no' => $request->phone_no,
                    'gurdian_name' => $request->gurdian_name,
                    'gurdian_phone_no' => $request->gurdian_phone_no,
                    'CNIC_image' => $CNIC_image,
                    'dateofbirth' => $request->dateofbirth,
                    'gender' => $request->gender,
                    'profile_image' => $profile_image,
                    'current_address' => $request->current_address
                ]);

                $teamrequest = TeamRequest::create([
                    'user_id' => Auth::user()->id,
                    'role_id' => $request->role_id,
                    'teamposition_id' => $request->position_id,
                    'team_id' => $id,
                ]);

                DB::commit();
                return response()->json(['data' => 'You have successfully requested for the team'], 201);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['data' => 'Oops. some error occur while requesting'], 500);
            }
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Check if the user has any teams
        if (Auth::user()->teamrequest()->count() > 0) {
            return response()->json(['data' => 'You have already requested to the team'], 403);
        }

        // Fetch team and roles if no user teams exist
        $team = Team::with('teamposition')->find($id); // Use find() for single record
        $roles = Role::where('name', '!=', 'super-admin')->get();

        return response()->json([
            'data' => [
                'roles' => $roles,
                'team' => $team
            ]
        ], 200);
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
