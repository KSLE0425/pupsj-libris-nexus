<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Specialty;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function index()
    {
        $courses = Course::orderBy('code')->get();
        $specialties = Specialty::orderBy('name')->get();
        return view('admin.programs.index', compact('courses', 'specialties'));
    }

    // Courses
    public function storeCourse(Request $request)
    {
        $data = $request->validate(['code' => 'required|unique:programs', 'name' => 'required']);
        $c = Course::create($data);

        AuditLogger::log('course_created', 'Created program/course: [' . $c->code . '] ' . $c->name, [
            'course_id' => $c->id,
            'code'      => $c->code,
            'name'      => $c->name,
        ], 'programs');

        return back()->with('message', 'Course added.');
    }

    public function updateCourse(Request $request, Course $course)
    {
        $data = $request->validate([
            'code' => 'required|unique:programs,code,'.$course->id,
            'name' => 'required'
        ]);
        $course->update($data);

        AuditLogger::log('course_updated', 'Updated program/course: [' . $course->code . '] ' . $course->name, [
            'course_id' => $course->id,
            'code'      => $course->code,
            'name'      => $course->name,
        ], 'programs');

        return back()->with('message', 'Course updated.');
    }

    public function destroyCourse(Course $course)
    {
        $code = $course->code;
        $name = $course->name;
        $id = $course->id;
        $course->delete();

        AuditLogger::log('course_deleted', 'Deleted program/course: [' . $code . '] ' . $name, [
            'course_id' => $id,
            'code'      => $code,
            'name'      => $name,
        ], 'programs');

        return back()->with('message', 'Program archived.');
    }

    // Specialties
    public function storeSpecialty(Request $request)
    {
        $data = $request->validate(['name' => 'required|unique:specialties']);
        $s = Specialty::create($data);

        AuditLogger::log('specialty_created', 'Created faculty specialty: ' . $s->name, [
            'specialty_id' => $s->id,
            'name'         => $s->name,
        ], 'programs');

        return back()->with('message', 'Specialty added.');
    }

    public function updateSpecialty(Request $request, Specialty $specialty)
    {
        $data = $request->validate(['name' => 'required|unique:specialties,name,'.$specialty->id]);
        $oldName = $specialty->name;
        $specialty->update($data);

        AuditLogger::log('specialty_updated', 'Updated faculty specialty from "' . $oldName . '" to "' . $specialty->name . '"', [
            'specialty_id' => $specialty->id,
            'old_name'     => $oldName,
            'new_name'     => $specialty->name,
        ], 'programs');

        return back()->with('message', 'Specialty updated.');
    }

    public function destroySpecialty(Specialty $specialty)
    {
        $name = $specialty->name;
        $id = $specialty->id;
        $specialty->delete();

        AuditLogger::log('specialty_deleted', 'Deleted faculty specialty: ' . $name, [
            'specialty_id' => $id,
            'name'         => $name,
        ], 'programs');

        return back()->with('message', 'Department archived.');
    }
}