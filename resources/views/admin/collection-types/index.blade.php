@extends('layouts.admin')

@section('content')
<div class="ct-page">
    <div class="page-header">
        <div>
            <h2>Collection Types</h2>
            <p class="page-subtitle">Manage custom collection categories for books. Archived types are hidden from the Add/Edit Book dropdown.</p>
        </div>
    </div>

    @if(session('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card" style="margin-bottom:2rem;">
        <div class="card-header">
            <h4>Add New Collection Type</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.collection-types.store') }}" class="form-row">
                @csrf
                <input type="text" name="name" placeholder="Enter collection type name" required class="form-control">
                <button type="submit" class="btn-primary">Add Collection Type</button>
            </form>
        </div>
    </div>

    {{-- Filter + Search bar --}}
    <div style="display:flex; flex-wrap:wrap; gap:12px; align-items:center; margin-bottom:1rem;">
        <div style="display:flex; gap:6px;">
            @foreach(['all' => 'All', 'active' => 'Active', 'archived' => 'Archived'] as $val => $label)
                <a href="{{ route('admin.collection-types.index', array_filter(['filter' => $val === 'all' ? null : $val, 'search' => $search ?: null])) }}"
                   style="padding:6px 16px; border-radius:20px; font-size:0.78rem; font-weight:600; text-decoration:none; border:1.5px solid;
                          {{ $filter === $val ? 'background:var(--pup-maroon);color:#fff;border-color:var(--pup-maroon);' : 'background:#fff;color:#555;border-color:#d1d5db;' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
        <div style="flex:1; min-width:200px; max-width:340px; position:relative;">
            <input id="ct-search" type="text" value="{{ $search }}" placeholder="Search collection types…"
                   style="width:100%; padding:7px 12px 7px 36px; border:1.5px solid #d1d5db; border-radius:20px; font-size:0.82rem; font-family:inherit; outline:none;"
                   oninput="liveSearch(this.value)">
            <svg style="position:absolute;left:11px;top:50%;transform:translateY(-50%);pointer-events:none;opacity:0.4;" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="data-table" id="ct-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th style="text-align:center;">A–Z Classification</th>
                        <th style="text-align:center;">Research Type &amp; Program</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($collectionTypes as $type)
                    <tr class="{{ $type->archived_at ? 'row-archived' : '' }}">
                        <td style="font-weight:500;">
                            <span id="ct-name-{{ $type->id }}">{{ $type->name }}</span>
                            @if(!$type->is_built_in)
                            <form id="ct-edit-form-{{ $type->id }}" method="POST"
                                  action="{{ route('admin.collection-types.update', $type) }}"
                                  style="display:none; margin-top:4px;">
                                @csrf @method('PUT')
                                <div style="display:flex;gap:6px;align-items:center;">
                                    <input type="text" name="name" value="{{ $type->name }}"
                                           style="padding:4px 8px;border:1px solid #d1d5db;border-radius:6px;font-size:0.85rem;flex:1;">
                                    <button type="submit" class="action-btn btn-unarchive" style="padding:4px 10px;">Save</button>
                                    <button type="button" class="action-btn" style="padding:4px 10px;"
                                            onclick="cancelEdit({{ $type->id }})">Cancel</button>
                                </div>
                            </form>
                            @endif
                        </td>

                        {{-- A–Z Classification toggle --}}
                        <td style="text-align:center;">
                            <form method="POST" action="{{ route('admin.collection-types.flags', $type) }}" style="display:inline;">
                                @csrf @method('PATCH')
                                <input type="hidden" name="has_research_type" value="{{ $type->has_research_type ? '1' : '0' }}">
                                <button type="submit" name="has_loc_classification" value="{{ $type->has_loc_classification ? '0' : '1' }}"
                                    class="toggle-btn {{ $type->has_loc_classification ? 'toggle-on' : 'toggle-off' }}">
                                    {{ $type->has_loc_classification ? 'ON' : 'OFF' }}
                                </button>
                            </form>
                        </td>

                        {{-- Research Type + Program toggle --}}
                        <td style="text-align:center;">
                            <form method="POST" action="{{ route('admin.collection-types.flags', $type) }}" style="display:inline;">
                                @csrf @method('PATCH')
                                <input type="hidden" name="has_loc_classification" value="{{ $type->has_loc_classification ? '1' : '0' }}">
                                <button type="submit" name="has_research_type" value="{{ $type->has_research_type ? '0' : '1' }}"
                                    class="toggle-btn {{ $type->has_research_type ? 'toggle-on' : 'toggle-off' }}">
                                    {{ $type->has_research_type ? 'ON' : 'OFF' }}
                                </button>
                            </form>
                        </td>

                        <td>
                            @if($type->is_built_in)
                                <span class="badge badge-builtin">Built-in</span>
                            @else
                                <span class="badge badge-custom">Custom</span>
                            @endif
                        </td>
                        <td>
                            <div class="actions-cell">
                                @if($type->archived_at)
                                    <form method="POST" action="{{ route('admin.collection-types.restore', $type) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="action-btn btn-unarchive">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                            Restore
                                        </button>
                                    </form>
                                @else
                                    @if(!$type->is_built_in)
                                        <button type="button" class="action-btn" onclick="startEdit({{ $type->id }})">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            Edit
                                        </button>
                                    @endif
                                    <form method="POST" action="{{ route('admin.collection-types.archive', $type) }}" style="display:inline;" onsubmit="return confirm('Archive this collection type? All active books under it will also be archived.')">
                                        @csrf
                                        <button type="submit" class="action-btn btn-archive">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
                                            Archive
                                        </button>
                                    </form>
                                    @if(!$type->is_built_in)
                                        <form method="POST" action="{{ route('admin.collection-types.destroy', $type) }}" style="display:inline;" onsubmit="return confirm('Permanently delete this collection type?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="action-btn btn-delete">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="padding:2rem; text-align:center; color:#6b7280;">No collection types found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
:root {
    --pup-maroon: #800000;
    --pup-maroon-dark: #5a0000;
    --pup-gold: #FFC72C;
    --pup-gold-dark: #e6b328;
    --bg-main: #f5f5f5;
    --text: #1f2937;
    --text-muted: #6b7280;
    --border: #e5e7eb;
    --radius: 12px;
    --shadow: 0 1px 3px rgba(0,0,0,0.08);
    --shadow-md: 0 4px 12px rgba(0,0,0,0.1);
}
.ct-page { max-width: 1100px; margin: 0 auto; }
.page-header { display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px; margin-bottom:24px; border-left:4px solid var(--pup-gold); padding-left:16px; }
.ct-page h2 { margin:0 0 6px; font-size:1.75rem; font-weight:700; color:var(--pup-maroon); letter-spacing:-0.025em; }
.page-subtitle { color:var(--text-muted); font-size:0.9375rem; margin:0; }
.alert-success { background:#ecfdf5; color:#065f46; padding:12px 16px; border-radius:8px; margin-bottom:1.5rem; border-left:4px solid #10b981; font-size:0.88rem; font-weight:500; }
.alert-danger  { background:rgba(128,0,0,0.07); color:#800000; padding:12px 16px; border-radius:8px; margin-bottom:1.5rem; border-left:4px solid #800000; font-size:0.88rem; font-weight:500; }
.card { background:white; border-radius:var(--radius); box-shadow:var(--shadow); border:1px solid var(--border); overflow:hidden; margin-bottom:1.75rem; }
.card-header { display:flex; justify-content:space-between; align-items:center; padding:1.125rem 1.5rem; border-bottom:1px solid var(--border); }
.card-header h4 { margin:0; font-weight:600; color:var(--pup-maroon); font-size:1.05rem; }
.card-body { padding:1.5rem; }
.form-row { display:flex; gap:1rem; flex-wrap:wrap; align-items:center; }
.form-control { flex:1; min-width:200px; padding:0.65rem 0.875rem; border:1px solid var(--border); border-radius:8px; font-size:0.875rem; font-family:inherit; }
.form-control:focus { outline:none; border-color:var(--pup-maroon); box-shadow:0 0 0 3px rgba(128,0,0,0.1); }
.btn-primary { background:var(--pup-gold); color:var(--pup-maroon); border:none; padding:0.65rem 1.5rem; border-radius:8px; font-weight:700; cursor:pointer; transition:all 0.2s; display:inline-flex; align-items:center; gap:6px; font-family:inherit; font-size:0.875rem; white-space:nowrap; }
.btn-primary:hover { background:var(--pup-gold-dark); transform:translateY(-1px); }
.table-responsive { overflow-x:auto; }
.data-table { width:100%; border-collapse:collapse; }
.data-table th { background:#6a0000; color:white; padding:11px 14px; text-align:left; font-weight:600; font-size:0.78rem; text-transform:uppercase; letter-spacing:0.5px; }
.data-table td { padding:11px 14px; border-bottom:1px solid var(--border); vertical-align:middle; font-size:0.875rem; }
.data-table tbody tr:hover { background:#fffbf0; }
.row-archived td { opacity:0.65; background:#fafafa; }

/* Badges */
.badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:0.73rem; font-weight:600; }
.badge-builtin  { background:#FFF7DC; color:#6b3d00; }
.badge-custom   { background:#ecfdf5; color:#065f46; }
.badge-active   { background:rgba(255,199,44,0.2); color:#5a2d00; }
.badge-archived { background:rgba(128,0,0,0.07); color:#800000; }

/* Toggle buttons */
.toggle-btn { border:none; padding:0.3rem 0.9rem; border-radius:6px; font-size:0.78rem; font-weight:600; cursor:pointer; min-width:56px; font-family:inherit; transition:all 0.15s; }
.toggle-on  { background:#800000; color:#fff; }
.toggle-on:hover  { background:#5a0000; }
.toggle-off { background:#f3f4f6; color:#6b7280; }
.toggle-off:hover { background:#e5e7eb; }

/* Action buttons */
.actions-cell { display:flex; gap:6px; flex-wrap:wrap; align-items:center; }
.action-btn { display:inline-flex; align-items:center; gap:4px; padding:5px 11px; border-radius:6px; font-size:0.75rem; font-weight:600; cursor:pointer; transition:all 0.18s; border:1.5px solid transparent; font-family:inherit; line-height:1.4; white-space:nowrap; }
.action-btn:hover { transform:translateY(-1px); }
.btn-archive  { background:rgba(128,0,0,0.05); color:#800000; border-color:rgba(128,0,0,0.35); }
.btn-archive:hover { background:#800000; color:white; }
.btn-unarchive { background:#FFF7DC; color:#6b3d00; border-color:#FFC72C; }
.btn-unarchive:hover { background:#FFC72C; color:#5a0000; }
.btn-delete   { background:#800000; color:white; border-color:#800000; }
.btn-delete:hover { background:#5a0000; }
</style>

<script>
function startEdit(id) {
    document.getElementById('ct-name-' + id).style.display = 'none';
    document.getElementById('ct-edit-form-' + id).style.display = 'block';
}
function cancelEdit(id) {
    document.getElementById('ct-name-' + id).style.display = '';
    document.getElementById('ct-edit-form-' + id).style.display = 'none';
}
function liveSearch(val) {
    const q = val.trim().toLowerCase();
    document.querySelectorAll('#ct-table tbody tr').forEach(function(row) {
        const name = row.querySelector('td:first-child')?.textContent?.toLowerCase() ?? '';
        row.style.display = (!q || name.includes(q)) ? '' : 'none';
    });
}
</script>
@endsection
