<?php

namespace App\Http\Controllers\Teammembers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendence;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendenceController extends Controller
{


    public function index(): JsonResponse
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

        return response()->json(['data' => $users], 200);
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

        return response()->json(['data' => $event], 200);
    }


    public function checkIn()
    {
        DB::beginTransaction();
        try {
            // Add your logic here
            $attandence = Attendence::create([
                'user_id' => Auth::user()->id,
                'employe_check_in' => Carbon::now(),
            ]);
            DB::commit();
            return response()->json(['data' => 'You are logged in successfully'], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['data' => 'Oops. some error occur while logging in'], 500);
        }
    }

    public function checkOut(Request $request)
    {
        // dd($request);

        DB::beginTransaction();
        try {
            // Add your logic here
            $checkin = Attendence::where('user_id', $request->user()->id)
                ->latest()
                ->first();

            $checkin->update([
                'employe_check_out' => Carbon::now(),
            ]);
            DB::commit();
            return response()->json(['data' => 'You are logged out successfully'], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['data' => 'Oops.some error occured'], 500);
        }
    }
}
