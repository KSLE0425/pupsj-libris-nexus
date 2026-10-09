<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Mail\FacultyApprovedMail;
use Illuminate\Support\Facades\Mail;

class FacultyApprovalController extends Controller
{
    public function index()
    {
        $faculties = Faculty::where('status', 'pending')->orderBy('created_at', 'desc')->get();
        return view('admin.pending-faculties', compact('faculties'));
    }

    public function approve($id)
    {
        $faculty = Faculty::findOrFail($id);
        $faculty->update(['status' => 'active']);
        Mail::to($faculty->email)->send(new FacultyApprovedMail($faculty));
        return back()->with('message', 'Faculty approved and notified.');
    }

    public function reject($id)
    {
        $faculty = Faculty::findOrFail($id);
        $faculty->delete(); // soft delete
        return back()->with('message', 'Faculty rejected.');
    }
}