<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KohaSyncLog extends Model
{
    protected $fillable = ['action', 'status', 'records_synced', 'message'];
}
