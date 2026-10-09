@extends('layouts.admin')

@section('content')
<div class="programs-page">
    <h2>Programs & Departments</h2>
    <p class="page-subtitle">Manage program offerings and faculty departments.</p>

    @if(session('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <!-- Tabs -->
    <div class="tab-bar">
        <button class="tab-btn active" onclick="switchTab('courses')">Program</button>
        <button class="tab-btn" onclick="switchTab('specialties')">Departments</button>
    </div>

    <!-- Courses Tab -->
    <div id="tab-courses" class="tab-panel">
        <!-- Add Program Form -->
        <div class="card">
            <div class="card-header">
                <h4>Add New Program</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.programs.courses.store') }}">
                    @csrf
                    <div class="form-row">
                        <input type="text" name="code" placeholder="Code (e.g., BSIT)" required class="form-control">
                        <input type="text" name="name" placeholder="Full Program Name" required class="form-control">
                        <button type="submit" class="btn-primary">Add Program</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Existing Programs Table -->
        <div class="card">
            <div class="card-header">
                <h4>Existing Program</h4>
                <span class="card-badge" id="coursesCountBadge">{{ $courses->count() }} Program</span>
            </div>
            <div class="filter-toolbar">
                <input type="text" id="coursesSearch" class="filter-input" placeholder="Search by code or name…" oninput="filterSortPrograms()">
                <select id="coursesSort" class="filter-select" onchange="filterSortPrograms()">
                    <option value="code-asc">Code A–Z</option>
                    <option value="code-desc">Code Z–A</option>
                    <option value="name-asc">Name A–Z</option>
                    <option value="name-desc">Name Z–A</option>
                </select>
            </div>
            <div class="table-responsive">
                <table class="data-table" id="coursesTable">
                    <thead><tr><th>Code</th><th>Name</th><th>Actions</th></tr></thead>
                    <tbody>
                        @foreach($courses as $course)
                        <tr data-code="{{ strtolower($course->code) }}" data-name="{{ strtolower($course->name) }}">
                            <td>{{ $course->code }}</td>
                            <td>{{ $course->name }}</td>
                            <td>
                                <button onclick="editProgram('{{ $course->id }}', '{{ $course->code }}', '{{ addslashes($course->name) }}')" class="action-btn edit">Edit</button>
                                <form method="POST" action="{{ route('admin.programs.courses.destroy', $course->id) }}" style="display:inline;" onsubmit="return confirm('Archive this program?');">
                                    @csrf @method('DELETE')
                                    <button class="action-btn delete">Archive</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Specialties Tab -->
    <div id="tab-specialties" class="tab-panel" style="display:none;">
        <div class="card">
            <div class="card-header"><h4>Add New Department</h4></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.programs.specialties.store') }}">
                    @csrf
                    <div class="form-row">
                        <input type="text" name="name" placeholder="Department name" required class="form-control">
                        <button type="submit" class="btn-primary">Add Department</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h4>Existing Departments</h4>
                <span class="card-badge" id="specialtiesCountBadge">{{ $specialties->count() }} {{ Str::plural('department', $specialties->count()) }}</span>
            </div>
            <div class="filter-toolbar">
                <input type="text" id="specialtiesSearch" class="filter-input" placeholder="Search by name…" oninput="filterSortDepartments()">
                <select id="specialtiesSort" class="filter-select" onchange="filterSortDepartments()">
                    <option value="name-asc">Name A–Z</option>
                    <option value="name-desc">Name Z–A</option>
                </select>
            </div>
            <div class="table-responsive">
                <table class="data-table" id="specialtiesTable">
                    <thead><tr><th>Name</th><th>Actions</th></tr></thead>
                    <tbody>
                        @foreach($specialties as $specialty)
                        <tr data-name="{{ strtolower($specialty->name) }}">
                            <td>{{ $specialty->name }}</td>
                            <td>
                                <button onclick="editSpecialty('{{ $specialty->id }}', '{{ addslashes($specialty->name) }}')" class="action-btn edit">Edit</button>
                                <form method="POST" action="{{ route('admin.programs.specialties.destroy', $specialty->id) }}" style="display:inline;" onsubmit="return confirm('Archive this department?');">
                                    @csrf @method('DELETE')
                                    <button class="action-btn delete">Archive</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Program Modal -->
    <div id="editCourseModal" class="modal-overlay" style="display:none;">
        <div class="modal-container">
            <div class="modal-header">
                <h4>Edit Program</h4>
                <button type="button" class="modal-close" onclick="closeProgModal()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" id="editCourseForm">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label>Program Code <span class="required">*</span></label>
                        <input type="text" name="code" id="editCourseCode" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Course Name <span class="required">*</span></label>
                        <input type="text" name="name" id="editCourseName" class="form-control" required>
                    </div>
                    <div class="modal-actions">
                        <button type="submit" class="btn-primary">Update Program</button>
                        <button type="button" onclick="closeProgModal()" class="btn-outline">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Program Modal -->
    <div id="editSpecialtyModal" class="modal-overlay" style="display:none;">
        <div class="modal-container">
            <div class="modal-header">
                <h4>Edit Program</h4>
                <button type="button" class="modal-close" onclick="closeSpecialtyModal()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" id="editSpecialtyForm">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label>Program Name <span class="required">*</span></label>
                        <input type="text" name="name" id="editSpecialtyName" class="form-control" required>
                    </div>
                    <div class="modal-actions">
                        <button type="submit" class="btn-primary">Update Program</button>
                        <button type="button" onclick="closeSpecialtyModal()" class="btn-outline">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.programs-page {
    --pup-maroon: #800000;
    --pup-maroon-dark: #5a0000;
    --pup-gold: #FFC72C;
    --pup-gold-dark: #e6b328;
    --bg-main: #f5f5f5;
    --text: #1f2937;
    --text-muted: #6b7280;
    --border: #e5e7eb;
    --radius: 12px;
    --shadow: 0 1px 3px rgba(0,0,0,0.1);
    --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1);
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 1rem;
}
.programs-page h2 { margin: 0 0 8px 0; font-size: 1.75rem; font-weight: 700; color: var(--pup-maroon); letter-spacing: -0.025em; }
.page-subtitle { color: var(--text-muted); font-size: 0.9375rem; margin-bottom: 1.75rem; }
.alert-success { background: #ecfdf5; color: #065f46; padding: 1rem 1.25rem; border-radius: 10px; margin-bottom: 1.5rem; border-left: 4px solid #10b981; font-size:0.875rem; }
.alert-danger  { background: rgba(128,0,0,0.07); color: #800000; padding: 1rem 1.25rem; border-radius: 10px; margin-bottom: 1.5rem; border-left: 4px solid #800000; font-size:0.875rem; }
.tab-bar { display: flex; gap: 0.5rem; margin-bottom: 1.5rem; }
.tab-btn { padding: 0.6rem 1.2rem; border: none; background: white; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s; }
.tab-btn.active { background: var(--pup-maroon); color: white; }
.card { background: white; border-radius: var(--radius); box-shadow: var(--shadow); border: 1px solid var(--border); overflow: hidden; margin-bottom: 1.75rem; }
.card-header { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); flex-wrap: wrap; gap: 0.75rem; }
.card-header h4 { margin: 0; font-weight: 600; color: var(--pup-maroon); font-size: 1.125rem; }
.card-badge { font-size: 0.75rem; color: var(--text-muted); background: var(--bg-main); padding: 0.375rem 0.75rem; border-radius: 20px; }
.card-body { padding: 1.5rem; }
.filter-toolbar { display: flex; gap: 0.75rem; padding: 0.85rem 1.5rem; border-bottom: 1px solid var(--border); flex-wrap: wrap; background: #fafafa; }
.filter-input { flex: 1; min-width: 180px; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: 8px; font-size: 0.875rem; font-family: inherit; }
.filter-input:focus { outline: none; border-color: var(--pup-maroon); box-shadow: 0 0 0 3px rgba(128,0,0,0.1); }
.filter-select { padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: 8px; font-size: 0.875rem; font-family: inherit; background: #fff; min-width: 140px; }
.form-row { display: flex; gap: 1rem; align-items: center; }
.form-control { flex: 1; padding: 0.6rem; border: 1px solid var(--border); border-radius: 8px; }
.form-control:focus { outline: none; border-color: var(--pup-maroon); box-shadow: 0 0 0 3px rgba(128,0,0,0.1); }
.btn-primary { background: var(--pup-gold); color: var(--pup-maroon); border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s; min-height: 44px; }
.btn-primary:hover { background: var(--pup-gold-dark); transform: translateY(-1px); }
.btn-outline { background: transparent; color: var(--pup-maroon); border: 2px solid var(--pup-maroon); padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.btn-outline:hover { background: var(--pup-maroon); color: white; }
.table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; margin: 0 -0.5rem; padding: 0 0.5rem; }
.data-table { width: 100%; border-collapse: collapse; min-width: 500px; }
.data-table th { background: var(--pup-maroon); color: white; padding: 1rem; text-align: left; font-weight: 600; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.5px; }
.data-table td { padding: 1rem; border-bottom: 1px solid var(--border); vertical-align: middle; font-size: 0.875rem; }
.data-table tbody tr:hover { background: #FEFCE8; }
.action-btn { display: inline-flex; align-items: center; gap: 4px; padding: 5px 11px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; cursor: pointer; transition: all 0.18s; border: 1.5px solid transparent; font-family: inherit; line-height: 1.4; white-space: nowrap; }
.action-btn:hover { transform: translateY(-1px); }
.action-btn.edit   { background: #FFF7DC; color: #6b3d00; border-color: #FFC72C; }
.action-btn.edit:hover { background: #FFC72C; color: #5a0000; }
.action-btn.delete { background: rgba(128,0,0,0.05); color: #800000; border-color: rgba(128,0,0,0.35); }
.action-btn.delete:hover { background: #800000; color: white; }

/* Modal Styles */
.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); display: flex; align-items: center; justify-content: center; z-index: 1100; }
.modal-container { background: white; border-radius: var(--radius); max-width: 500px; width: 90%; box-shadow: var(--shadow-md); }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); }
.modal-header h4 { margin: 0; font-weight: 600; color: var(--pup-maroon); font-size: 1.125rem; }
.modal-close { background: none; border: none; cursor: pointer; padding: 0.25rem; display: flex; align-items: center; justify-content: center; color: var(--text-muted); transition: color 0.2s; }
.modal-close:hover { color: var(--pup-maroon); }
.modal-body { padding: 1.5rem; }
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text); font-size: 0.875rem; }
.form-group .required { color: #EF4444; }
.form-group input { width: 100%; padding: 0.625rem 0.875rem; border: 1px solid var(--border); border-radius: 8px; font-size: 0.875rem; transition: all 0.2s; }
.form-group input:focus { outline: none; border-color: var(--pup-maroon); box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.1); }
.modal-actions { display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem; }

@media (max-width: 768px) {
    .programs-page { padding: 0 0.5rem; }
    .programs-page h2 { font-size: 1.5rem; }
    .form-row { flex-direction: column; align-items: stretch; }
    .btn-primary { width: 100%; justify-content: center; }
    .card-header { flex-direction: column; text-align: center; }
    .modal-container { width: 95%; }
    .modal-header, .modal-body { padding: 1rem; }
    .modal-actions { flex-direction: column; }
    .modal-actions .btn-primary, .modal-actions .btn-outline { width: 100%; justify-content: center; }
}
</style>

<script>
function switchTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');
    document.getElementById('tab-courses').style.display = tab === 'courses' ? 'block' : 'none';
    document.getElementById('tab-specialties').style.display = tab === 'specialties' ? 'block' : 'none';
}

function filterSortPrograms() {
    const q = (document.getElementById('coursesSearch').value || '').toLowerCase().trim();
    const sort = document.getElementById('coursesSort').value;
    const tbody = document.querySelector('#coursesTable tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    let visible = 0;
    rows.forEach(row => {
        const code = row.dataset.code || '';
        const name = row.dataset.name || '';
        const match = !q || code.includes(q) || name.includes(q);
        row.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    const [field, dir] = sort.split('-');
    rows.sort((a, b) => {
        const av = a.dataset[field] || '';
        const bv = b.dataset[field] || '';
        return dir === 'asc' ? av.localeCompare(bv) : bv.localeCompare(av);
    });
    rows.forEach(r => tbody.appendChild(r));
    const badge = document.getElementById('coursesCountBadge');
    if (badge) badge.textContent = visible + (visible === 1 ? ' Program' : ' Programs');
}

function filterSortDepartments() {
    const q = (document.getElementById('specialtiesSearch').value || '').toLowerCase().trim();
    const sort = document.getElementById('specialtiesSort').value;
    const tbody = document.querySelector('#specialtiesTable tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    let visible = 0;
    rows.forEach(row => {
        const name = row.dataset.name || '';
        const match = !q || name.includes(q);
        row.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    const dir = sort.split('-')[1];
    rows.sort((a, b) => {
        const av = a.dataset.name || '';
        const bv = b.dataset.name || '';
        return dir === 'asc' ? av.localeCompare(bv) : bv.localeCompare(av);
    });
    rows.forEach(r => tbody.appendChild(r));
    const badge = document.getElementById('specialtiesCountBadge');
    if (badge) badge.textContent = visible + (visible === 1 ? ' department' : ' departments');
}

function editProgram(id, code, name) {
    const form = document.getElementById('editCourseForm');
    form.action = '/admin/programs/courses/' + id;
    document.getElementById('editCourseCode').value = code;
    document.getElementById('editCourseName').value = name;
    document.getElementById('editCourseModal').style.display = 'flex';
}

function closeProgModal() {
    document.getElementById('editCourseModal').style.display = 'none';
}

function editSpecialty(id, name) {
    const form = document.getElementById('editSpecialtyForm');
    form.action = '/admin/programs/specialties/' + id;
    document.getElementById('editSpecialtyName').value = name;
    document.getElementById('editSpecialtyModal').style.display = 'flex';
}

function closeSpecialtyModal() {
    document.getElementById('editSpecialtyModal').style.display = 'none';
}

// Close modals when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const courseModal = document.getElementById('editCourseModal');
    if (courseModal) {
        courseModal.addEventListener('click', function(e) {
            if (e.target === courseModal) closeProgModal();
        });
    }
    const specialtyModal = document.getElementById('editSpecialtyModal');
    if (specialtyModal) {
        specialtyModal.addEventListener('click', function(e) {
            if (e.target === specialtyModal) closeSpecialtyModal();
        });
    }
});
</script>
@endsection