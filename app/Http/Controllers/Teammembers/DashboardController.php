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

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        // $testingquery = ;
        dd(User::with('courses')->toSql());


        $teams = Team::whereNotNull('company_id')->get();
        return view('welcome', compact('teams'));
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
        foreach ($roles as $role) {
            $roles = $role->name;
        }

        switch ($roles) {
            case 'team-captain':
                return redirect()->intended('teamcaptain/dashboard');
                break;

            case 'bussiness-administration-manager':
                return redirect()->intended('teamcaptain/dashboard');
                break;

            case 'team-leader':
                return redirect()->intended('teamcaptain/dashboard');
                break;

            case 'human-resources':
                return redirect()->intended('teamcaptain/dashboard');
                break;

            case 'team-member':
                return redirect()->intended('employee/dashboard');
                break;

            default:
                notify()->error('you have not been assigned any role till now', 'Does Not have any role');
                return redirect()->intended('/');
        }
    }


    public function coursedashboard()
    {
        // dd(Auth::user());
        $roles = Auth::user()->roles;
        $bit = 0;
        foreach ($roles as $role) {
            $bit = 1;
        }

        switch ($bit) {
            case '1':
                return to_route('course.index');
                break;

            default:
                notify()->error('You are not the member of our team', 'Not Allowed');
                return redirect()->intended('/');
        }
    }


    public function userteamrequest(string $id, Request $request)
    {
        $userid = Auth::user()->id;
        $teamrequest = TeamRequest::where('user_id', $userid)->count();
        // dd($teamrequest);
        if ($teamrequest > 0) {
            notify()->warning('You have already applied for the request wait for it');
            return to_route('welcome.index');
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

            if ($userdetail) {
                $teamrequest = TeamRequest::create([
                    'user_id' => Auth::user()->id,
                    'role_id' => $request->role_id,
                    'teamposition_id' => $request->position_id,
                    'team_id' => $id,
                ]);

                if ($teamrequest) {
                    notify()->success('You have successfully Applied for Team Now wait for request accept');
                    return to_route('welcome.index');
                }
            }
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $teams = Team::with('teamposition')->where('id', $id)->get();
        $roles = Role::where('name', '!=', 'super-admin')->get();
        // dd($roles);
        return view('dashboard', compact('teams', 'roles'));
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
