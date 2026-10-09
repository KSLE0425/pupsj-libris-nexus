<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportPreset extends Model
{
    protected $fillable = [
        'report_type',
        'name',
        'description',
        'options',
        'is_default',
        'created_by',
    ];

    protected $casts = [
        'options'    => 'array',
        'is_default' => 'boolean',
    ];
}
