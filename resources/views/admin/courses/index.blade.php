@extends('layouts.admin')

@section('content')
<div class="courses-page">
    <h2>Manage Courses</h2>
    <p class="page-subtitle">Add, edit, or remove course offerings in the system.</p>

    @if(session('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif

    <!-- Add Course Card -->
    <div class="card">
        <div class="card-header">
            <h4>Add New Program</h4>
            <span class="card-badge">New Entry</span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.courses.store') }}">
                @csrf
                <div class="form-grid">
                    <div class="form-group">
                        <label>Program Code <span class="required">*</span></label>
                        <input type="text" name="code" class="form-control" required placeholder="e.g., BSIT">
                        <small class="field-hint">Unique program identifier</small>
                    </div>
                    <div class="form-group">
                        <label>Program Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g., Bachelor of Science in Information Technology">
                        <small class="field-hint">Full program title</small>
                    </div>
                    <div class="form-group form-submit">
                        <button type="submit" class="btn-primary">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 5v14M5 12h14"/>
                            </svg>
                            Add Program
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Existing Courses Table -->
    <div class="card">
        <div class="card-header">
            <h4>Existing Courses</h4>
            <span class="card-badge">{{ $courses->count() }} total program</span>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="col-code">Program</th>
                        <th class="col-name">Program Name</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($courses as $course)
                    <tr>
                        <td class="col-code">{{ $course->code }}</td>
                        <td class="col-name">{{ $course->name }}</td>
                        <td class="col-actions">
                            <div class="actions-wrapper">
                                <button onclick="editCourse('{{ $course->id }}', '{{ $course->code }}', '{{ $course->name }}')" class="action-btn edit">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"/>
                                        <polygon points="18 2 22 6 12 16 8 16 8 12 18 2"/>
                                    </svg>
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('admin.courses.destroy', $course->id) }}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn delete" onclick="return confirm('Archive this program? It can be restored later.')">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <line x1="18" y1="6" x2="6" y2="18"/>
                                            <line x1="6" y1="6" x2="18" y2="18"/>
                                        </svg>
                                        Archive
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($courses->isEmpty())
        <div class="empty-state">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <p>No Program found</p>
            <small>Add your first program using the form above</small>
        </div>
        @endif
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal-overlay" style="display:none;">
    <div class="modal-container">
        <div class="modal-header">
            <h4>Edit Program</h4>
            <button type="button" class="modal-close" onclick="closeModal()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <form method="POST" id="editForm">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Course Code <span class="required">*</span></label>
                    <input type="text" name="code" id="editCode" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Course Name <span class="required">*</span></label>
                    <input type="text" name="name" id="editName" class="form-control" required>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn-primary">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"/>
                            <polygon points="18 2 22 6 12 16 8 16 8 12 18 2"/>
                        </svg>
                        Update Course
                    </button>
                    <button type="button" onclick="closeModal()" class="btn-outline">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* PUP Theme Variables - Matching Books Page */
.courses-page {
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

/* Page Header */
.courses-page h2 {
    margin: 0 0 8px 0;
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--pup-maroon);
    letter-spacing: -0.025em;
    word-break: break-word;
}

.courses-page .page-subtitle {
    color: var(--text-muted);
    font-size: 0.9375rem;
    margin-bottom: 1.75rem;
}

/* Alert */
.alert-success {
    background: #ECFDF5;
    color: #065F46;
    padding: 1rem 1.25rem;
    border-radius: 10px;
    margin-bottom: 1.5rem;
    border-left: 4px solid #10B981;
}

/* Cards */
.card {
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
    overflow: hidden;
    margin-bottom: 1.75rem;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--border);
    background: white;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.card-header h4 {
    margin: 0;
    font-weight: 600;
    color: var(--pup-maroon);
    font-size: 1.125rem;
}

.card-badge {
    font-size: 0.75rem;
    color: var(--text-muted);
    background: var(--bg-main);
    padding: 0.375rem 0.75rem;
    border-radius: 20px;
    margin-top: -2px;
    display: inline-block;
}

.card-body {
    padding: 1.5rem;
}

/* Form Grid - 3 columns matching Books page */
.form-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
    align-items: center;
}

.form-group {
    margin-bottom: 0;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: var(--text);
    font-size: 0.875rem;
}

.form-group .required {
    color: #EF4444;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 0.625rem 0.875rem;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: 0.875rem;
    font-family: inherit;
    transition: all 0.2s;
    background: white;
    min-height: 44px;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--pup-maroon);
    box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.1);
}

.field-hint {
    display: block;
    margin-top: 0.375rem;
    font-size: 0.7rem;
    color: var(--text-muted);
}

.form-submit {
    display: flex;
    align-items: center;
}

/* Buttons */
.btn-primary {
    background: var(--pup-gold);
    color: var(--pup-maroon);
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    min-height: 44px;
    width: 100%;
    justify-content: center;
}

.btn-primary:hover {
    background: var(--pup-gold-dark);
    transform: translateY(-2px);
}

.btn-outline {
    background: transparent;
    color: var(--pup-maroon);
    border: 2px solid var(--pup-maroon);
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.875rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    transition: all 0.2s;
    min-height: 44px;
}

.btn-outline:hover {
    background: var(--pup-maroon);
    color: white;
    transform: translateY(-2px);
}

/* Table */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    margin: 0 -0.5rem;
    padding: 0 0.5rem;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 500px;
}

.data-table .col-code {
    width: 20%;
}
.data-table .col-name {
    width: 55%;
}
.data-table .col-actions {
    width: 25%;
    text-align: center;
}

.data-table th {
    background: var(--pup-maroon);
    color: white;
    padding: 1rem 1rem;
    text-align: left;
    font-weight: 600;
    font-size: 0.8125rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.data-table th.col-actions {
    text-align: center;
}

.data-table td {
    padding: 1rem 1rem;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
    font-size: 0.875rem;
    word-break: break-word;
}

.data-table td.col-actions {
    text-align: center;
}

.data-table tbody tr {
    transition: background 0.2s;
}

.data-table tbody tr:hover {
    background: #FEFCE8;
}

/* Action Buttons in Table */
.actions-wrapper {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.875rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
    min-width: 70px;
    min-height: 34px;
}

.action-btn.edit {
    background: #EEF2FF;
    color: #4F46E5;
}

.action-btn.edit:hover {
    background: #4F46E5;
    color: white;
    transform: translateY(-1px);
}

.action-btn.delete {
    background: #FEF2F2;
    color: #991B1B;
}

.action-btn.delete:hover {
    background: #EF4444;
    color: white;
    transform: translateY(-1px);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem;
    color: var(--text-muted);
    font-size: 0.9375rem;
}

.empty-state svg {
    margin-bottom: 1rem;
    opacity: 0.5;
    stroke: var(--text-muted);
    max-width: 100%;
}

.empty-state p {
    margin: 0 0 0.25rem 0;
}

.empty-state small {
    font-size: 0.8125rem;
    opacity: 0.7;
}

/* Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1100;
}

.modal-container {
    background: white;
    border-radius: var(--radius);
    max-width: 500px;
    width: 90%;
    box-shadow: var(--shadow-md);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--border);
}

.modal-header h4 {
    margin: 0;
    font-weight: 600;
    color: var(--pup-maroon);
    font-size: 1.125rem;
}

.modal-close {
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
    transition: color 0.2s;
}

.modal-close:hover {
    color: var(--pup-maroon);
}

.modal-body {
    padding: 1.5rem;
}

.modal-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 1.5rem;
}

/* ============================================ */
/* MOBILE RESPONSIVENESS */
/* ============================================ */

@media (max-width: 1024px) {
    .courses-page {
        padding: 0 0.75rem;
    }
}

@media (max-width: 768px) {
    .courses-page {
        padding: 0 0.5rem;
    }
    
    .courses-page h2 {
        font-size: 1.5rem;
    }
    
    .page-subtitle {
        font-size: 0.875rem;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
        align-items: stretch;
    }
    
    .form-submit {
        margin-top: 0.5rem;
    }
    
    .btn-primary {
        width: 100%;
        justify-content: center;
    }
    
    .card-header {
        flex-direction: column;
        gap: 0.5rem;
        text-align: center;
        padding: 1rem;
    }
    
    .card-header h4 {
        font-size: 1rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .table-responsive {
        margin: 0 -0.5rem;
        padding: 0 0.5rem;
    }
    
    .data-table th,
    .data-table td {
        padding: 0.75rem 0.5rem;
        font-size: 0.75rem;
    }
    
    .action-btn {
        padding: 0.25rem 0.625rem;
        min-width: 60px;
        font-size: 0.7rem;
    }
    
    .modal-container {
        width: 95%;
    }
    
    .modal-header {
        padding: 1rem;
    }
    
    .modal-body {
        padding: 1rem;
    }
    
    .modal-actions {
        flex-direction: column;
    }
    
    .modal-actions .btn-primary,
    .modal-actions .btn-outline {
        width: 100%;
        justify-content: center;
    }
    
    .empty-state {
        padding: 2rem 1rem;
    }
}

@media (max-width: 480px) {
    .courses-page h2 {
        font-size: 1.3rem;
    }
    
    .data-table th,
    .data-table td {
        padding: 0.5rem 0.375rem;
        font-size: 0.7rem;
    }
    
    .action-btn {
        padding: 0.2rem 0.5rem;
        min-width: 50px;
        font-size: 0.65rem;
    }
    
    .action-btn svg {
        width: 12px;
        height: 12px;
    }
}
</style>

<script>
function editCourse(id, code, name) {
    const form = document.getElementById('editForm');
    form.action = '/admin/courses/' + id;
    document.getElementById('editCode').value = code;
    document.getElementById('editName').value = name;
    document.getElementById('editModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('editModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });
    }
});
</script>
@endsection