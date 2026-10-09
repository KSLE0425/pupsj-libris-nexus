<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;

class ActivityLogController extends Controller
{
    public function index()
    {
        return response()->json(
            ActivityLog::orderByDesc('created_at')->limit(200)->get()
        );
    }
}
