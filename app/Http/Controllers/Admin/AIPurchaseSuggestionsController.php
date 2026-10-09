<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookRequisition;
use App\Services\AISuggestionService;
use Illuminate\Http\Request;

class AIPurchaseSuggestionsController extends Controller
{
    protected $aiService;

    public function __construct(AISuggestionService $aiService)
    {
        $this->aiService = $aiService;
        // No auth middleware needed
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $suggestions = $this->aiService->getPurchaseSuggestions();
            return response()->json(['suggestions' => $suggestions]);
        }

        $requisitions = BookRequisition::with(['student', 'faculty'])
            ->orderByDesc('created_at')
            ->get();

        return view('admin.ai-purchase-suggestions', compact('requisitions'));
    }

    public function updateRequisitionStatus(Request $request, BookRequisition $requisition)
    {
        $request->validate(['status' => 'required|in:approved,ordered,received,rejected', 'admin_notes' => 'nullable|string|max:500']);
        $requisition->update(['status' => $request->status, 'admin_notes' => $request->admin_notes]);
        return back()->with('message', 'Requisition status updated.');
    }

    public function checkNewEditions($bookId)
    {
        $book = Book::findOrFail($bookId);
        $result = $this->aiService->checkNewEditions($book->title, $book->author);

        return response()->json(['result' => $result]);
    }
}
