<?php

namespace App\Repositories;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BookRepository
{
    public function getAll()
    {
        return Book::orderBy('title')->get();
    }

    public function findById($id)
    {
        return Book::findOrFail($id);
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'title'              => 'required|string',
            'author'             => 'required|string',
            'isbn'               => 'nullable|string',
            'accession_number'   => 'nullable|string|max:100',
            'barcode'            => 'nullable|string|unique:books,barcode',
            'subject'            => 'nullable|string',
            'keywords'           => 'nullable|string',
            'publisher'          => 'nullable|string',
            'publication_year'   => 'nullable|integer|min:1800|max:'.date('Y'),
            'edition'            => 'nullable|string',
            'collection'         => 'nullable|string',
            'collection_type_id' => 'nullable|integer|exists:collection_types,id',
            'research_type'      => 'nullable|in:capstone,thesis',
            'loc_number'         => 'nullable|string',
            'shelf_location'     => 'nullable|string',
            'course_id'          => 'nullable|integer',
            'is_new_acquisition' => 'nullable|boolean',
            'is_donation'        => 'nullable|boolean',
            'is_filipino_author' => 'nullable|boolean',
            'is_ph_published'    => 'nullable|boolean',
            'is_ph_subject'      => 'nullable|boolean',
            'cover_image'        => 'nullable|image|max:6144',
            'toc_images.*'       => 'nullable|image|max:6144',
        ]);

        // Sync collection string from FK
        if (!empty($validated['collection_type_id'])) {
            $ct = \App\Models\CollectionType::find($validated['collection_type_id']);
            if ($ct) {
                $validated['collection'] = $ct->name;
            }
        }

        $validated['status']             = 'available';
        $validated['copies']             = 1;
        $validated['is_new_acquisition'] = $request->has('is_new_acquisition') ? 1 : 0;

        // Handle Cover Page Image
        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')->store('book_covers', 'public');
            $validated['title_cover_image_path'] = $coverPath;
        }

        $book = Book::create($validated);

        // Handle multiple TOC images
        if ($request->hasFile('toc_images')) {
            foreach ($request->file('toc_images') as $image) {
                $path = $image->store('book_toc', 'public');
                $book->tocImages()->create(['path' => $path]);
            }
        }

        return $book;
    }

    public function update(Book $book, Request $request)
    {
        $validated = $request->validate([
            'title'              => 'sometimes|string',
            'author'             => 'sometimes|string',
            'isbn'               => 'nullable|string',
            'accession_number'   => 'nullable|string|max:100',
            'barcode'            => 'nullable|string|unique:books,barcode,'.$book->id,
            'subject'            => 'nullable|string',
            'keywords'           => 'nullable|string',
            'publisher'          => 'nullable|string',
            'publication_year'   => 'nullable|integer|min:1800|max:'.date('Y'),
            'edition'            => 'nullable|string',
            'collection'         => 'nullable|string',
            'collection_type_id' => 'nullable|integer|exists:collection_types,id',
            'research_type'      => 'nullable|in:capstone,thesis',
            'loc_number'         => 'nullable|string',
            'shelf_location'     => 'nullable|string',
            'copies'             => 'nullable|integer|min:0',
            'course_id'          => 'nullable|integer',
            'is_new_acquisition' => 'nullable|boolean',
            'is_donation'        => 'nullable|boolean',
            'is_filipino_author' => 'nullable|boolean',
            'is_ph_published'    => 'nullable|boolean',
            'is_ph_subject'      => 'nullable|boolean',
            'cover_image'        => 'nullable|image|max:6144',
            'toc_images.*'       => 'nullable|image|max:6144',
            'remove_cover'       => 'nullable|boolean',
            'removed_toc_images' => 'nullable|array',
        ]);

        if (!empty($validated['collection_type_id'])) {
            $ct = \App\Models\CollectionType::find($validated['collection_type_id']);
            if ($ct) {
                $validated['collection'] = $ct->name;
            }
        }

        $validated['is_new_acquisition'] = $request->has('is_new_acquisition') ? 1 : 0;
        if ($request->filled('copies')) {
            $validated['copies'] = max(0, (int) $request->input('copies'));
        }

        // Cover image removal / replacement
        if ($request->boolean('remove_cover') && $book->title_cover_image_path) {
            Storage::disk('public')->delete($book->title_cover_image_path);
            $validated['title_cover_image_path'] = null;
        }

        if ($request->hasFile('cover_image')) {
            if ($book->title_cover_image_path) {
                Storage::disk('public')->delete($book->title_cover_image_path);
            }
            $coverPath = $request->file('cover_image')->store('book_covers', 'public');
            $validated['title_cover_image_path'] = $coverPath;
        }

        $book->update($validated);

        // Delete individually removed TOC images
        if ($request->filled('removed_toc_images') && is_array($request->removed_toc_images)) {
            $toDelete = $book->tocImages()->whereIn('id', $request->removed_toc_images)->get();
            foreach ($toDelete as $img) {
                Storage::disk('public')->delete($img->path);
                $img->delete();
            }
        }

        // Add newly uploaded TOC images
        if ($request->hasFile('toc_images')) {
            foreach ($request->file('toc_images') as $image) {
                $path = $image->store('book_toc', 'public');
                $book->tocImages()->create(['path' => $path]);
            }
        }

        return $book;
    }

    public function archive(Book $book)
    {
        $book->status = 'archived';
        $book->save();
        return $book;
    }

    public function unarchive(Book $book)
    {
        $book->status = 'available';
        $book->save();
        return $book;
    }

    public function condemn(Book $book, string $reason, ?string $proofImage = null)
    {
        $book->status = 'condemned';
        $book->is_condemned = true;
        $book->condemned_at = now();
        $book->condemnation_reason = $reason;
        $book->copies = 0;
        if ($proofImage !== null) {
            $book->proof_image = $proofImage;
        }
        $book->save();
        return $book;
    }

    public function uncondemn(Book $book)
    {
        $book->status = 'available';
        $book->is_condemned = false;
        $book->condemned_at = null;
        $book->condemnation_reason = null;
        $book->copies = 1;
        $book->save();
        return $book;
    }

    public function forceDelete(Book $book)
    {
        $book->forceDelete();
    }
}