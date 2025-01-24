<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    // public function __construct()
    // {
    //     $this->middleware('role:Super-Admin');
    // }


    public function index()
    {
        $company = Company::withCount(['team'])->get();

        $company_count = $company->count();
        $teamcaptains = User::role('team-captain')->count();
        $teams = Team::count();
        return view('teamcaptain.company.index', compact('company', 'company_count' , 'teamcaptains' , 'teams'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('teamcaptain.company.add');
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

        $company_image = uploadFile($request, 'company_image');


        $company = Company::create([
            'company_name' => $request->company_name,
            'slug' => Str::slug($request->company_name, '-'), // Corrected 'comapny_name' to 'company_name'
            'company_description' => $request->company_description,
            'company_image' => $company_image,
        ]);

        if ($company) {
            notify()->success('Company has been added successfully ⚡️');
            return to_route('company.index');
        }
        notify()->error('Oppppsss ......Company has not been added ⚡️');
        return to_route('company.add');
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
        $company = Company::find($id);
        return view('teamcaptain.company.edit' , compact('company'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'company_name' => 'required|max:255',
            'company_description' => 'required|max:400',
            'company_image' => 'nullable|image|size|max:4000',
        ]);

        $company = Company::findOrFail($id);

        if ($request->hasFile('company_image')) {
            $company_image = uploadFile($request, 'company_image');
        } else {
            $company_image = $company->company_image;
        }

        $company->update([
            'company_name' => trim($request->company_name),
            'slug' => Str::slug($request->company_name, '-'),
            'description' => trim($request->description),
            'company_image' => $company_image,
        ]);

        notify()->success('Company has been updated successfully ⚡️');
        return to_route('company.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $id)
    {
        $delete = $id->delete();
        if ($delete) {
            notify()->success('Data is deleted success fully');

        } else {
            notify()->warning('Something ... went wrong');
        }
        return to_route('company.add');
    }
}
