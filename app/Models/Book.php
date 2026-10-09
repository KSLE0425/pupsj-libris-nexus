<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'author',
        'isbn',
        'accession_number',
        'barcode',
        'subject',
        'keywords',
        'publisher',
        'publication_year',
        'edition',
        'collection',
        'loc_number',
        'status',
        'copies',
        'category',
        'is_new_acquisition',
        'qr_hash',
        'shelf_location',
        'is_condemned',
        'condemned_at',
        'condemnation_reason',
        'condemned_by',
        'last_borrower_info',
        'proof_image',
        'research_type',
        'course_id',
        'specialty_id',
        'collection_type_id',
        'is_donation',
        'is_filipino_author',
        'is_ph_published',
        'is_ph_subject',
        'archived_by_collection',
        'title_cover_image_path',
        'toc_image_path',
    ];

    protected $casts = [
        'is_condemned'      => 'boolean',
        'condemned_at'      => 'datetime',
        'is_new_acquisition' => 'boolean',
        'is_donation'       => 'boolean',
        'is_filipino_author' => 'boolean',
        'is_ph_published'   => 'boolean',
        'is_ph_subject'     => 'boolean',
    ];

    protected static function booted()
    {
        static::saving(function ($book) {
            if (!empty($book->barcode)) {
                $book->qr_hash = hash('sha256', $book->barcode);
            }
        });
    }

    public function usages()
    {
        return $this->hasMany(BookUsage::class);
    }

   public function notifications()
{
    return $this->hasMany(BookNotification::class);
}

    public function tocImages()
    {
        return $this->hasMany(BookTocImage::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function collectionType()
    {
        return $this->belongsTo(CollectionType::class);
    }

    public function getLocFullNameAttribute()
    {
        $map = [
            'A' => 'A - General Works',
            'B' => 'B - Philosophy, Psychology, Religion',
            'C' => 'C - Auxiliary Sciences of History',
            'D' => 'D - World History and History of Europe, Asia, Africa',
            'E-F' => 'E-F - History of the Americas',
            'G' => 'G - Geography, Anthropology, Recreation',
            'H' => 'H - Social Sciences',
            'J' => 'J - Political Science',
            'K' => 'K - Law',
            'L' => 'L - Education',
            'M' => 'M - Music and Books on Music',
            'N' => 'N - Fine Arts',
            'P' => 'P - Language and Literature',
            'Q' => 'Q - Science',
            'R' => 'R - Medicine',
            'S' => 'S - Agriculture',
            'T' => 'T - Technology',
            'U' => 'U - Military Science',
            'V' => 'V - Naval Science',
            'Z' => 'Z - Bibliography, Library Science',
        ];

        return $map[$this->loc_number] ?? $this->loc_number;
    }
}