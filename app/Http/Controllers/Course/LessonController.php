<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LessonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Course $course)
    {
        $course->load('lessons');
        return response()->json(['data' => [$course]], 200);
    }

    public function create(Course $course)
    {
        return response()->json(['data' => [$course]], 200);
    }

    public function store(Request $request, Course $course)
    {
        // Dump the entire request data (Remove after debugging)
        // dd($request->all());
        // Validate incoming request data
        $validated = $request->validate([
            'title.*' => 'required|string|max:255',
            'should_completed_in.*' => 'required|numeric',
            'url.*' => 'nullable|url',
            'content.*' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            // Loop through each lesson's details and create a new Lesson record
            foreach ($validated['title'] as $index => $name) {
                $lesson = new Lesson();
                $lesson->course_id = $course->id; // Associate lesson with the course
                $lesson->title = $name;
                $lesson->should_completed_in = $validated['should_completed_in'][$index]; // Assign the duration
                $lesson->url = $validated['url'][$index] ?? null; // Handle optional URL
                $lesson->content = $validated['content'][$index]; // Add lesson content

                $lesson->save(); // Save the lesson record to the database
            }

            DB::commit(); // Commit the transaction
            return response()->json(['data' => 'Data has been saved successfully'], 201);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback in case of error
            return response()->json(['data' => 'Oppsss ! something went wrong'], 500);
        }

        return redirect()->route('course.index');
    }

    public function show(Course $course, Lesson $lesson)
    {
        // Eager load course with the specific lesson
        $lesson->load('course');
        return response()->json(['data' => [$lesson]], 200);
    }

    public function edit(Course $course, Lesson $lesson)
    {
        return response()->json(['data' => [$course, $lesson]], 200);
    }

    public function update(Request $request, Course $course, Lesson $lesson)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $lesson->update($validatedData);
            DB::commit();
            return response()->json(['data' => 'Lesson has been updated successfully', $course], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['data' => 'Oops. Something went wrong'], 500);
        }
    }

    public function destroy(Course $course, Lesson $lesson)
    {
        DB::beginTransaction();
        try {
            $lesson->delete();
            DB::commit();
            return response()->json(['data' => ['/n has been ( ) successfully', $course]], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['data' => 'Oops. Something went wrong'], 500);
        }
    }
}
