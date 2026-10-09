<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\AISuggestionService;
use Illuminate\Support\Facades\Auth;

class AIRecommendationsController extends Controller
{
    protected $aiService;

    public function __construct(AISuggestionService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function index()
    {
        $student = Auth::guard('student')->user();
        $recommendations = $this->aiService->getStudentRecommendations($student->id);

        return view('student.ai-recommendations', compact('recommendations'));
    }
}
