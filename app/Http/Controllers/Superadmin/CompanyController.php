<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Team;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{

    public function index()
    {
        // Fetch company data with related team counts
        $companies = Company::withCount('team')->get();

        // Count various entities
        $companyCount = $companies->count();
        $teamCaptainsCount = User::role('team-captain')->count();
        $teamsCount = Team::count();

        // Structure the response
        $response = [
            'companies' => $companies,
            'statistics' => [
                'company_count' => $companyCount,
                'team_captains' => $teamCaptainsCount,
                'teams' => $teamsCount,
            ],
        ];

        // Return response with a clear structure
        return response()->json(['data' => $response], 200);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // return view('teamcaptain.company.add');
        return response()->json(['data' => 'Hi function exists but donot do anything'], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|unique:companies,company_name|max:255', // Corrected 'company_nam' to 'company_name'
            'company_description' => 'required|max:400',
            'company_image' => 'nullable|image|max:4000',
        ]);

        DB::beginTransaction();

        try {
            $company_image = uploadFile($request, 'company_image');
            $company = Company::create([
                'company_name' => $request->company_name,
                'slug' => Str::slug($request->company_name, '-'), // Corrected 'comapny_name' to 'company_name'
                'company_description' => $request->company_description,
                'company_image' => $company_image,
            ]);
            DB::commit();
            return response()->json(['data' => 'Company has been created successfully'], 200);
        } catch (Exception $e) {

            DB::rollBack();
            return response()->json(['data' => 'Some error is occuring while storing your Data'], 500);
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
        $company = Company::findorFail($id);
        return response()->json(['data' => $company], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => [
                'required|max:255',
                Rule::unique('companies', 'company_name')->ignore($id),
            ], // Corrected 'company_nam' to 'company_name'
            'company_description' => 'required|max:400',
            'company_image' => 'nullable|image|max:4000',
        ]);

        if ($validator->fails()) {
            return response()->json(['data' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        $company = Company::findOrFail($id);

        if ($request?->hasFile('company_image')) {
            $company_image = uploadFile($request, 'company_image');
        } else {
            $company_image = $company->company_image;
        }

        try {
            $company->update([
                'company_name' => trim($request->company_name),
                'slug' => Str::slug($request->company_name, '-'),
                'description' => trim($request->description),
                'company_image' => $company_image,
            ]);
            DB::commit();
            return response()->json(['data' => 'Company has been updated successfully'], 201);
        } catch (Exception $e) {

            DB::rollBack();
            return response()->json(['data' => 'Oppsss Something went wrong'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $id)
    {
        DB::beginTransaction();
        try {
            // Add your logic here
            $id->delete();
            DB::commit();
            return response()->json(['data' => 'Company has been deleted successfully'], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['data' => 'Oops. Company is not deleted'], 500);
        }
    }
}
