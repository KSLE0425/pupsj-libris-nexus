<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollectionType extends Model
{
    protected $fillable = ['name', 'is_built_in', 'archived_at', 'has_loc_classification', 'has_research_type'];

    protected $casts = [
        'is_built_in'            => 'boolean',
        'archived_at'            => 'datetime',
        'has_loc_classification' => 'boolean',
        'has_research_type'      => 'boolean',
    ];

    public function books()
    {
        return $this->hasMany(Book::class, 'collection_type_id');
    }
}