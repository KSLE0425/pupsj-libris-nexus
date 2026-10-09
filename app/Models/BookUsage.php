<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookUsage extends Model
{
    protected $fillable = [
        'student_id',
        'librarian_id',
        'book_id',
        'time_in',
        'due_date',
        'time_out',
        'status',
        'faculty_id',
        'usage_context',
        'remarks',
        'acknowledged_by_admin1',
        'acknowledged_at_admin1',
        'acknowledged_by_admin2',
        'acknowledged_at_admin2',
        'acknowledged_by_admin3',
        'acknowledged_at_admin3',
        'is_overdue_flagged',
        'return_kiosk',
        'return_acknowledged_at',
    ];

    public function faculty()
{
    return $this->belongsTo(Faculty::class);
}

    protected $casts = [
        'time_in' => 'datetime',
        'time_out' => 'datetime',
        'due_date' => 'date',
        'acknowledged_at_admin1' => 'datetime',
        'acknowledged_at_admin2' => 'datetime',
        'acknowledged_at_admin3' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function librarian()
    {
        return $this->belongsTo(Librarian::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function damageReports()
    {
        return $this->hasMany(LibraryDamageReport::class);
    }

    /** Whether this borrow is still out and past its due date (or flagged overdue). */
    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'active'
            && ($this->is_overdue_flagged || ($this->due_date && $this->due_date->lt(today())));
    }

    /**
     * Borrow-history filters used by the student & faculty portals.
     * Keys: q (title/author), status (borrowed|returned|overdue), from, to (Y-m-d, on time_in).
     */
    public function scopeHistoryFilters($query, array $filters)
    {
        if (!empty($filters['q'])) {
            $term = $filters['q'];
            $query->whereHas('book', function ($b) use ($term) {
                $b->where('title', 'like', "%{$term}%")
                  ->orWhere('author', 'like', "%{$term}%");
            });
        }

        switch ($filters['status'] ?? null) {
            case 'borrowed':
                $query->where('status', 'active');
                break;
            case 'returned':
                $query->where('status', 'completed');
                break;
            case 'overdue':
                $query->where('status', 'active')->where(function ($q) {
                    $q->where('is_overdue_flagged', true)
                      ->orWhereDate('due_date', '<', today());
                });
                break;
        }

        if (!empty($filters['from'])) {
            $query->whereDate('time_in', '>=', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $query->whereDate('time_in', '<=', $filters['to']);
        }

        return $query;
    }
}
