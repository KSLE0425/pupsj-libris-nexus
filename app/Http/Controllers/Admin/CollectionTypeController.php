<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\CollectionType;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CollectionTypeController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $filter = $request->input('filter', 'all');
        $search = $request->input('search', '');

        $query = CollectionType::query();
        if ($filter === 'active')   $query->whereNull('archived_at');
        if ($filter === 'archived') $query->whereNotNull('archived_at');
        if ($search !== '')         $query->where('name', 'like', '%' . $search . '%');

        $collectionTypes = $query->orderByRaw('archived_at IS NOT NULL')->orderBy('name')->get();
        return view('admin.collection-types.index', compact('collectionTypes', 'filter', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:collection_types,name',
        ]);

        $ct = CollectionType::create([
            'name'       => $validated['name'],
            'is_built_in' => false,
        ]);

        AuditLogger::log('collection_type_created', 'Created collection type "' . $ct->name . '"', [
            'collection_type_id' => $ct->id,
            'name'               => $ct->name,
        ], 'collection_types');

        return redirect()->route('admin.collection-types.index')
            ->with('message', 'Collection type added successfully.');
    }

    public function update(Request $request, CollectionType $collectionType)
    {
        if ($collectionType->is_built_in) {
            return back()->with('error', 'Cannot modify built-in collection types.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:collection_types,name,' . $collectionType->id,
        ]);

        $oldName = $collectionType->name;
        $collectionType->update($validated);

        AuditLogger::log('collection_type_updated', 'Renamed collection type from "' . $oldName . '" to "' . $collectionType->name . '"', [
            'collection_type_id' => $collectionType->id,
            'old_name'           => $oldName,
            'new_name'           => $collectionType->name,
        ], 'collection_types');

        return redirect()->route('admin.collection-types.index')
            ->with('message', 'Collection type updated successfully.');
    }

    public function updateFlags(Request $request, CollectionType $collectionType)
    {
        $collectionType->update([
            'has_loc_classification' => $request->boolean('has_loc_classification'),
            'has_research_type'      => $request->boolean('has_research_type'),
        ]);

        AuditLogger::log('collection_type_updated', 'Updated classification flags for collection type "' . $collectionType->name . '"', [
            'collection_type_id' => $collectionType->id,
            'name'               => $collectionType->name,
        ], 'collection_types');

        return back()->with('message', '"' . $collectionType->name . '" flags updated.');
    }

    public function archive(CollectionType $collectionType)
    {
        try {
            DB::transaction(function () use ($collectionType) {
                $collectionType->update(['archived_at' => now()]);

                // Archive all non-archived, non-condemned books in this collection
                Book::where('collection_type_id', $collectionType->id)
                    ->where('status', '!=', 'archived')
                    ->whereNull('condemned_at')
                    ->update(['status' => 'archived', 'archived_by_collection' => true]);
            });

            AuditLogger::log('collection_type_archived', 'Archived collection type "' . $collectionType->name . '" and all contained books', [
                'collection_type_id' => $collectionType->id,
                'name'               => $collectionType->name,
            ], 'collection_types');
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to archive collection type: ' . $e->getMessage());
        }

        return redirect()->route('admin.collection-types.index')
            ->with('message', 'Collection type archived. All active books under it have been archived too.');
    }

    public function restore(CollectionType $collectionType)
    {
        try {
            DB::transaction(function () use ($collectionType) {
                $collectionType->update(['archived_at' => null]);

                // Only unarchive books that were archived BY this collection type archiving
                // (books that were already archived before keep their archived status)
                Book::where('collection_type_id', $collectionType->id)
                    ->where('archived_by_collection', true)
                    ->update(['status' => 'available', 'archived_by_collection' => null]);
            });

            AuditLogger::log('collection_type_restored', 'Restored collection type "' . $collectionType->name . '" and its books', [
                'collection_type_id' => $collectionType->id,
                'name'               => $collectionType->name,
            ], 'collection_types');
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to restore collection type: ' . $e->getMessage());
        }

        return redirect()->route('admin.collection-types.index')
            ->with('message', 'Collection type restored. Previously active books have been unarchived.');
    }

    public function destroy(CollectionType $collectionType)
    {
        if ($collectionType->is_built_in) {
            return back()->with('error', 'Cannot delete built-in collection types.');
        }

        $name = $collectionType->name;
        $id = $collectionType->id;
        $collectionType->delete();

        AuditLogger::log('collection_type_deleted', 'Deleted collection type "' . $name . '"', [
            'collection_type_id' => $id,
            'name'               => $name,
        ], 'collection_types');

        return redirect()->route('admin.collection-types.index')
            ->with('message', 'Collection type deleted successfully.');
    }
}
