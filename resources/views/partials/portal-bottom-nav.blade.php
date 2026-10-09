{{-- Mobile bottom tab bar for the student & faculty portals. Expects $guard = 'student' | 'faculty'. --}}
@php
    $isFaculty = $guard === 'faculty';
    $tabs = [
        [
            'label'  => 'Home',
            'route'  => "{$guard}.dashboard",
            'active' => request()->routeIs("{$guard}.dashboard"),
            'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
        ],
        [
            'label'  => 'Browse',
            'route'  => $isFaculty ? 'faculty.catalog' : 'student.borrow',
            'active' => $isFaculty
                ? request()->routeIs('faculty.catalog', 'faculty.book.show')
                : request()->routeIs('student.borrow', 'student.book.show'),
            'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>',
        ],
        [
            'label'  => 'History',
            'route'  => "{$guard}.history",
            'active' => request()->routeIs("{$guard}.history"),
            'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        ],
        [
            'label'  => 'Penalties',
            'route'  => "{$guard}.penalties.index",
            'active' => request()->routeIs("{$guard}.penalties.*"),
            'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        ],
        [
            'label'  => 'Profile',
            'route'  => "{$guard}.profile",
            'active' => request()->routeIs("{$guard}.profile"),
            'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>',
        ],
    ];
@endphp
<nav class="bottom-nav" aria-label="Primary">
    @foreach($tabs as $tab)
        <a href="{{ route($tab['route']) }}" class="bn-item {{ $tab['active'] ? 'active' : '' }}" @if($tab['active']) aria-current="page" @endif>
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">{!! $tab['icon'] !!}</svg>
            <span>{{ $tab['label'] }}</span>
        </a>
    @endforeach
</nav>
