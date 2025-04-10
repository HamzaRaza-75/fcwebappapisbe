<?php

namespace App\Http\Controllers\TeamCaptain;

use App\Charts\ClientTaskChart;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Task;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clients = Client::with(['tasksmilestones'])
            ->withCount(['tasks'])
            ->withSum('tasks', 'estimated_budjet')
            ->get();

        $totalclients = $clients->count();


        $tasks = Task::whereNotNull('client_id')->get();

        $totalearnings = $tasks->sum('estimated_budjet');
        $totalworths = $tasks->sum('word_count');
        $totalprojects = $tasks->count();

        foreach ($clients as $client) {
            $client->totalhours = 0; // Initialize totalhours to 0

            foreach ($client->tasksmilestones as $taskmilestone) {
                $client->totalhours += $taskmilestone->worth; // Sum the worth from task milestones
            }
        }

        foreach ($clients as $client) {
            $client->totalwordcount = 0; // Initialize totalhours to 0

            foreach ($client->tasksmilestones as $taskmilestone) {
                $client->totalwordcount += $taskmilestone->word_count; // Sum the worth from task milestones
            }
        }

        return response()->json(['data' => [$clients, $totalclients, $totalprojects, $totalearnings, $totalworths]], 200);
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'countary_name' => 'required',
        ]);

        DB::beginTransaction();
        try {
            Client::create($request->all());
            DB::commit();
            return response()->json(['data' => 'Client Has Been added successfully'], 200);
        } catch (Exception $e) {

            DB::rollBack();
            return response()->json(['data' => 'Oppsss ! something went wrong'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $client = Client::with([
            'tasks' => ['taskmilestones', 'team'],
        ])
            ->withCount('tasks')->findOrFail($id);

        $totalTaskMilestones = $client->tasks->sum(function ($task) {
            return $task->taskmilestones->sum('worth');
        });

        $totalWordCount = number_format($client->tasks->sum('word_count'));

        $active_projects = $client->tasks->where('status', 'incomplete')->count();
        $cancelled_projects = $client->tasks->where('status', 'cancelled')->count();
        $completed_projects = $client->tasks->where('status', 'completed')->count();

        $response = ['client' => $client, 'chartData' => [$cancelled_projects, $completed_projects, $active_projects], 'totalTaskMilestones' => $totalTaskMilestones, 'totalWordCount' => $totalWordCount];

        return response()->json(['data' => $response], 200);
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
        DB::beginTransaction();
        try {
            // Add your logic here
            Client::find($id)->delete();
            DB::commit();
            return response()->json(['data' => 'Client has been deleted successfully'], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['data' => 'Oops. client  is not deleted'], 500);
        }
    }
}
