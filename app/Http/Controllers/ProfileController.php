<?php

namespace App\Http\Controllers;

use App\Charts\EmployeeChart;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Actionplan;
use App\Models\TaskMilestone;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {

        $users = User::withCount([
            'actionplan as without_task_revision' => function ($query) {
                $query->whereDoesntHave('taskrevision');
            },
            'actionplan as with_task_revision' => function ($q) {
                $q->whereHas('taskrevision');
            }
        ])
            ->with(['roles', 'userteam', 'skills'])
            ->findOrFail(Auth::user()->id);
        $users->loadCount('tasksAssignedTo');
        $users->load('latestTask');
        // dd($users);

        $monthlyMilestones = Actionplan::selectRaw("
        MONTH(action_plan_starting_datetime) as month,
        COUNT(*) as total,
        SUM(CASE WHEN revision = 1 THEN 1 ELSE 0 END) as revisions,
        SUM(CASE WHEN revision = 0 THEN 1 ELSE 0 END) as accepted
    ")
            ->where('submited_by', Auth::id())
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


        return view('user.edit', [
            'user' => $users,
            // 'chart' => $chart->build($months, $approved, $revisions),
        ]);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'skill_tags.*' => 'required|string',
        ]);

        $user = Auth::user();

        // Create the skill tags for the user
        foreach ($validated['skill_tags'] as $skillTag) {
            $user->skills()->create([
                'skills_tags' => $skillTag,
            ]);
        }


        return to_route('profile.edit');
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
