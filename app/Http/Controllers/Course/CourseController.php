<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $courses = Course::with(['creater'])->where('status', 'approved')->withCount('lessons')->get();
        $selfcourse = Course::where('creater_id', Auth::id())->get();
        return response()->json(['data' => [$courses, $selfcourse]], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // do the createor logic here
        // if user has creator access then he can go to this route

        // return view('employess.course.dashboard.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'course_name' => 'required|string|max:60',
            'course_description' => 'required|string',
            'platform' => 'required|string|max:255',
            'login' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'duration_in_days' => 'required|integer',
            'platform_url' => 'nullable|url',
            'course_image' => 'required|image',
        ]);

        $course_image = uploadFile($request, 'course_image');

        $user = User::find(Auth::id());

        $status = 'pending';

        foreach ($user->roles as $role) {
            if ($role->name === 'team-captain') {
                $status = 'approved';
                break;
            }
        }

        DB::beginTransaction();
        try {
            $course = new Course();
            $course->creater_id = Auth::user()->id;
            $course->course_name = $validatedData['course_name'];
            $course->course_description = $validatedData['course_description'];
            $course->status = $status;
            $course->platform = $validatedData['platform'];
            $course->login = $validatedData['login'];
            $course->password = $validatedData['password'];
            $course->course_image = $course_image;
            $course->duration_in_days = $validatedData['duration_in_days'];
            $course->platform_url = $validatedData['platform_url'];
            $course->save();

            DB::commit();
            return response()->json(['data' => 'Course has been created successfully'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['data' => 'Oppsss sorry something went wrong'], 500);
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
        $course = Course::findOrFail($id);
        // dd($course);
        return response()->json(['data' => [$course]], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'course_name' => 'required|string|max:60',
            'course_description' => 'required|string',
            'platform' => 'required|string|max:255',
            'login' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'duration_in_days' => 'required|integer',
            'platform_url' => 'nullable|url',
            'course_image' => 'nullable|image', // Not required, only update if provided
        ]);

        DB::beginTransaction();
        try {
            $course = Course::findOrFail($id);
            $course->course_name = $validatedData['course_name'];
            $course->course_description = $validatedData['course_description'];
            $course->platform = $validatedData['platform'];
            $course->login = $validatedData['login'];
            $course->password = $validatedData['password'];
            $course->duration_in_days = $validatedData['duration_in_days'];
            $course->platform_url = $validatedData['platform_url'];

            if ($request->hasFile('course_image')) {
                $course->course_image = uploadFile($request, 'course_image');
            }

            $course->save();

            DB::commit();
            return response()->json(['data' => 'Data hasbeen saved successfully'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['data' => 'Oppsss ! something went wrong'], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        DB::beginTransaction();
        try {
            Course::findOrFail($id)->delete();
            DB::commit();
            return response()->json(['data' => 'Course has been deleted successfully'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['data' => 'Oops. something went wrong'], 500);
        }
    }
}
