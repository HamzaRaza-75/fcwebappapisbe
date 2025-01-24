<?php

namespace App\Http\Controllers\Teammembers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendence;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class AttendenceController extends Controller
{


    public function index(): View
    {

        $users = User::with('userteam')->find(Auth::user()->id);
        $logged_team = $users->userteam->pluck('id')->toArray();
        $logged_team_users = Team::whereIn('id', $logged_team)
            ->with(['userteam'])
            ->get(); // Keep it as a collection, do not convert to array yet

        // Flatten the userteam relationships and reset keys
        $users = $logged_team_users->pluck('userteam')
            ->flatten(1)
            ->unique('id')
            ->values()
            ->all();


        return view('teamcaptain.attandance.index', compact('users'));
    }

    public function show(string $id)
    {

        $attandence = Attendence::select(
            'user_id',
            'employe_check_in',
            'employe_check_out',
            'absent',
            'created_at'
        )->where('user_id', $id)->get();

        $event = $attandence->map(function ($att) {
            // Assuming 'employe_check_in' and 'employe_check_out' are time or datetime fields
            $checkIn = Carbon::parse($att->employe_check_in);
            $checkOut = Carbon::parse($att->employe_check_out);

            $duration = $checkOut->diffInHours($checkIn);

            return [
                'title' => $duration . ' hours worked',
                'start' =>  $att->created_at->toDateString(),
            ];
        });

        return response()->json($event);
    }


    public function checkIn()
    {

        $attandence = Attendence::create([
            'user_id' => Auth::user()->id,
            'employe_check_in' => Carbon::now(),
        ]);

        return redirect()->back()->with('success', 'Checked in successfully');
    }

    public function checkOut(Request $request)
    {
        // dd($request);

        $checkin = Attendence::where('user_id', $request->user()->id)
            ->latest()
            ->first();

        $checkin->update([
            'employe_check_out' => Carbon::now(),
        ]);

        // Redirect back with success message
        return redirect()->back()->with('success', 'Checked out successfully');
    }
}
