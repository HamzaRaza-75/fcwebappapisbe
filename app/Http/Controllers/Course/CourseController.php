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

        return view('employess.course.dashboard.index', compact('courses', 'selfcourse'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('employess.course.dashboard.create');
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
            notify()->success('Your Course Has been added successfully.', 'Course Created Successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            notify()->success('Oppsss !!! Something went wrong . Course is not created.', 'Error');
        }
        return to_route('course.index');
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
    public function edit(string $id): View
    {
        $course = Course::findOrFail($id);
        // dd($course);
        return view('employess.course.dashboard.edit', compact('course'));
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
            notify()->success('Your Course has been updated successfully.', 'Course Updated Successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            notify()->error('Oops! Something went wrong. The course could not be updated.', 'Error');
        }

        return to_route('course.index');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Course::findOrFail($id)->delete();
        notify()->success('The course which you have created is deleted successfully', 'Course Deleted');
        return redirect()->back();
    }
}
