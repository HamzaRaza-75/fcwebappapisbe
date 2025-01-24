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
    public function index(Course $course) : View
    {
        $course->load('lessons');

        return view('employess.course.dashboard.lessons.index', compact('course'));
    }

    public function create(Course $course)
    {
        return view('employess.course.dashboard.lessons.create', compact('course'));
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
            notify()->success('Your lectures are added successfully', 'Lectures Added Successfully');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback in case of error
            notify()->error('Oops! Something went wrong. Kindly contact the developer for help' . $e->getMessage(), 'Lectures Not Added');
        }

        return redirect()->route('course.index');
    }

    public function show(Course $course, Lesson $lesson)
    {
        // Eager load course with the specific lesson
        $lesson->load('course');

        return view('lessons.show', compact('course', 'lesson'));
    }

    public function edit(Course $course, Lesson $lesson)
    {
        return view('lessons.edit', compact('course', 'lesson'));
    }

    public function update(Request $request, Course $course, Lesson $lesson)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $lesson->update($validatedData);

        return redirect()->route('courses.lessons.index', $course);
    }

    public function destroy(Course $course, Lesson $lesson)
    {
        $lesson->delete();

        return redirect()->route('courses.lessons.index', $course);
    }
}
