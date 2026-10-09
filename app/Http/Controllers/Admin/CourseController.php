<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::orderBy('code')->get();
        return view('admin.courses.index', compact('courses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['code' => 'required|unique:programs', 'name' => 'required']);
        Course::create($data);
        return back()->with('message', 'Course added.');
    }

    public function update(Request $request, Course $course)
    {
        $data = $request->validate([
            'code' => 'required|unique:programs,code,'.$course->id,
            'name' => 'required'
        ]);
        $course->update($data);
        return back()->with('message', 'Course updated.');
    }

    public function destroy(Course $course)
    {
        $course->delete();
        return back()->with('message', 'Program archived.');
    }
}