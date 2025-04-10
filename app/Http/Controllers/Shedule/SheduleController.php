<?php

namespace App\Http\Controllers\Shedule;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Shedule;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class SheduleController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $shedules = Shedule::usershedule()->orderBy('end_time')->get();
        $shedulecount = $shedules->count();
        return response()->json(['data' => $shedules], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'shedule_name' => 'required|string|max:255',
            'shedule_description' => 'nullable|string',
            'end_time' => 'required|date|after_or_equal:today',
        ]);

        DB::beginTransaction();
        try {
            // Add your logic here

            Auth::user()->shedules()->create($request->all());

            DB::commit();
            return response()->json(['data' => 'Shedule has been added successfully'], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['data' => 'Oops. shedule  is not added'], 500);
        }
    }

    public function edit(Shedule $shedule)
    {
        Gate::authorize('update', $shedule);

        return response()->json(['data' => $shedule], 200);
    }

    public function update(Request $request, Shedule $shedule)
    {
        Gate::authorize('update', $shedule);

        $request->validate([
            'shedule_name' => 'required|string|max:255',
            'shedule_description' => 'nullable|string',
            'end_time' => 'required|date|after_or_equal:today',
        ]);

        DB::beginTransaction();
        try {
            // Add your logic here

            $shedule->shedules()->update($request->all());

            DB::commit();
            return response()->json(['data' => 'Shedule has been updated successfully'], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['data' => 'Oops. shedule is not updated'], 500);
        }
    }

    public function destroy(Shedule $schedule)
    {

        Gate::authorize('update', $schedule);

        DB::beginTransaction();
        try {
            // Add your logic here
            $schedule->delete();
            DB::commit();
            return response()->json(['data' => 'Shedule has been deleted successfully'], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['data' => 'Oops. shedule  is not deleted'], 500);
        }
    }
}
