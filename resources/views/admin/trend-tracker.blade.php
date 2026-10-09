@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">

<h2>📊 Trend Tracker</h2>
<p class="page-subtitle">Analyze borrowing trends by course and subject keywords</p>

{{-- Filter by Course --}}
<div class="filter-form">
    <form method="GET" action="{{ route('admin.trend-tracker') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label fw-bold">Filter by Course</label>
            <select name="course" class="form-select" onchange="this.form.submit()">
                <option value="all" {{ $course == 'all' ? 'selected' : '' }}>All Programs</option>
                @foreach($courses as $c)
                    <option value="{{ $c }}" {{ $course == $c ? 'selected' : '' }}>{{ $c }}</option>
                @endforeach
            </select>
        </div>
        {{-- <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Apply Filter</button>
        </div> --}}
    </form>
</div>

{{-- Statistics Cards --}}
<div class="stats-grid">
    <div class="stat-card">
        <h4>Total Borrows</h4>
        <p class="stat-number">{{ number_format($statistics['total_borrows']) }}</p>
    </div>
    <div class="stat-card">
        <h4>Active Borrows</h4>
        <p class="stat-number">{{ number_format($statistics['active_borrows']) }}</p>
    </div>
    <div class="stat-card">
        <h4>Total Students</h4>
        <p class="stat-number">{{ number_format($statistics['total_students']) }}</p>
    </div>
    <div class="stat-card">
        <h4>Total Books</h4>
        <p class="stat-number">{{ number_format($statistics['total_books']) }}</p>
    </div>
</div>

<div class="row">
    {{-- Trending Subjects --}}
    <div class="col-md-6">
        <div class="chart-container">
            <h5 class="mb-3">📚 Trending Subjects</h5>
            <div class="trend-list">
                @if($trends['by_subject']->count() > 0)
                    @foreach($trends['by_subject'] as $trend)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $trend->subject ?? 'Uncategorized' }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $trend->program }}</small>
                                </div>
                                <span class="badge bg-primary">{{ $trend->borrow_count }} borrows</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-primary" 
                                     style="width: {{ min(100, ($trend->borrow_count / max($trends['by_subject']->first()->borrow_count, 1)) * 100) }}%">
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted text-center py-4">No subject data available</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Top Borrowed Books --}}
    <div class="col-md-6">
        <div class="chart-container">
            <h5 class="mb-3">Top Borrowed Books</h5>
            <div class="trend-list">
                @if($topBooks->count() > 0)
                    @foreach($topBooks as $book)
                        <div class="mb-3 pb-2 border-bottom">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>{{ Str::limit($book->title, 40) }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ Str::limit($book->author, 30) }} • {{ $book->subject ?? 'No subject' }}
                                    </small>
                                    <br>
                                    <small class="text-muted">{{ $book->program }}</small>
                                </div>
                                <span class="badge bg-success">{{ $book->borrow_count }} borrows</span>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted text-center py-4">No book data available</p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Borrows by Course Chart --}}
<div class="chart-container">
    <h5 class="mb-3">📊 Borrows by Course</h5>
    <canvas id="courseChart" style="max-height: 300px; width: 100%;"></canvas>
</div>

{{-- Monthly Trend Chart --}}
<div class="chart-container">
    <h5 class="mb-3">📈 Monthly Borrowing Trend</h5>
    <canvas id="monthlyChart" style="max-height: 300px; width: 100%;"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Course Chart
    const courseCtx = document.getElementById('courseChart').getContext('2d');
    new Chart(courseCtx, {
        type: 'bar',
        data: {
            labels: @json($statistics['borrows_by_course']->pluck('program')),
            datasets: [{
                label: 'Number of Borrows',
                data: @json($statistics['borrows_by_course']->pluck('total')),
                backgroundColor: '#800000',
                borderRadius: 5,
                barPercentage: 0.7,
                categoryPercentage: 0.8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.raw.toLocaleString() + ' borrows';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Monthly Trend Chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: @json($statistics['monthly_trend']->pluck('month')),
            datasets: [{
                label: 'Borrows per Month',
                data: @json($statistics['monthly_trend']->pluck('total')),
                borderColor: '#800000',
                backgroundColor: 'rgba(128,0,0,0.1)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#800000',
                pointBorderColor: '#800000',
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.raw.toLocaleString() + ' borrows';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
});
</script>
@endsection