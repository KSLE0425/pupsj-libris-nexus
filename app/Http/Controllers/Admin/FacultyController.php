<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Specialty;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class FacultyController extends Controller
{
    public function index()
    {
        $faculties = Faculty::with('specialties')->orderBy('last_name')->get();
        return view('admin.faculties.index', compact('faculties'));
    }

    public function edit(Faculty $faculty)
    {
        $specialties = Specialty::orderBy('name')->get();
        return view('admin.faculties.edit', compact('faculty', 'specialties'));
    }

    public function restore($id, Request $request)
    {
        $faculty = Faculty::onlyTrashed()->findOrFail($id);
        $faculty->restore();

        AuditLogger::log('faculty_restored', 'Restored faculty account: ' . $faculty->first_name . ' ' . $faculty->last_name . ' (' . ($faculty->employee_id ?? $faculty->email) . ')', [
            'affected_user' => $faculty->first_name . ' ' . $faculty->last_name,
            'faculty_id'    => $faculty->id,
            'employee_id'   => $faculty->employee_id,
            'department'    => $faculty->department,
        ], 'users');

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Faculty member restored successfully.']);
        }
        return redirect()->back()->with('message', 'Faculty member restored successfully.')->with('active_tab', 'faculty');
    }

    public function update(Request $request, Faculty $faculty)
    {
        $data = $request->validate([
            'employee_id' => 'required|unique:faculties,employee_id,'.$faculty->id,
            'first_name'  => 'required',
            'last_name'   => 'required',
            'email'       => 'required|email|unique:faculties,email,'.$faculty->id,
            'department'  => 'nullable|string',
            'specialties' => 'nullable|array',
            'specialties.*' => 'exists:specialties,id',
        ]);

        $faculty->update($data);
        $faculty->specialties()->sync($request->specialties ?? []);

        AuditLogger::log('faculty_updated', 'Updated faculty member: ' . $faculty->first_name . ' ' . $faculty->last_name . ' (' . ($faculty->employee_id ?? $faculty->email) . ')', [
            'affected_user' => $faculty->first_name . ' ' . $faculty->last_name,
            'faculty_id'    => $faculty->id,
            'employee_id'   => $faculty->employee_id,
            'department'    => $faculty->department,
        ], 'users');

        return redirect()->back()->with('message', 'Faculty updated.')->with('active_tab', 'faculty');
    }

    public function destroy(Faculty $faculty, Request $request)
    {
        $name = $faculty->first_name . ' ' . $faculty->last_name;
        $empId = $faculty->employee_id;
        $facultyId = $faculty->id;
        $faculty->delete();

        AuditLogger::log('faculty_archived', 'Archived faculty member: ' . $name . ' (' . ($empId ?? 'ID: ' . $facultyId) . ')', [
            'affected_user' => $name,
            'faculty_id'    => $facultyId,
            'employee_id'   => $empId,
        ], 'users');

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Faculty member archived successfully.']);
        }
        return redirect()->back()->with('message', 'Faculty member archived successfully.')->with('active_tab', 'faculty');
    }

    public function archived()
    {
        $faculties = Faculty::onlyTrashed()->with('specialties')->orderBy('deleted_at', 'desc')->get();
        return view('admin.faculties.archived', compact('faculties'));
    }
}