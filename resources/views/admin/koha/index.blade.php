@extends('layouts.admin')

@section('title', 'Koha ILS Integration')

@section('content')
<style>
.koha-page { max-width: 860px; margin: 0 auto; }
.koha-header { border-left: 4px solid #FFC72C; padding-left: 16px; margin-bottom: 24px; }
.koha-header h1 { margin: 0 0 4px; font-size: 1.6rem; font-weight: 700; color: #800000; }
.koha-header p  { margin: 0; color: #6b7280; }
.k-card { background: white; border-radius: 12px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.08); overflow: hidden; margin-bottom: 20px; }
.k-card-header { padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: space-between; }
.k-card-header h3 { margin: 0; font-size: 1rem; font-weight: 600; color: #800000; }
.k-card-body { padding: 1.5rem; }
.k-field { margin-bottom: 16px; }
.k-label { display: block; font-size: 0.8rem; font-weight: 600; color: #374151; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.4px; }
.k-input { width: 100%; padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.875rem; font-family: inherit; transition: border-color 0.2s; }
.k-input:focus { outline: none; border-color: #800000; box-shadow: 0 0 0 3px rgba(128,0,0,0.1); }
.k-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; cursor: pointer; border: none; transition: all 0.18s; font-family: inherit; }
.k-btn-primary   { background: #800000; color: white; }
.k-btn-primary:hover   { background: #5a0000; }
.k-btn-gold   { background: #FFC72C; color: #800000; }
.k-btn-gold:hover   { background: #e6b328; }
.k-btn-outline { background: white; color: #374151; border: 1.5px solid #d1d5db; }
.k-btn-outline:hover { background: #f3f4f6; }
.k-badge { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: 0.72rem; font-weight: 600; }
.k-badge-success { background: #ECFDF5; color: #065F46; }
.k-badge-error   { background: #FEF2F2; color: #991B1B; }
.k-badge-off     { background: #F3F4F6; color: #6b7280; }
.k-toggle { display: flex; align-items: center; gap: 10px; }
.k-toggle input[type=checkbox] { width: 18px; height: 18px; accent-color: #800000; cursor: pointer; }
.k-sync-row { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
.k-log { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 16px; font-size: 0.82rem; color: #374151; }
.k-log-key { font-weight: 600; color: #6b7280; }
.alert { padding: 0.875rem 1.125rem; border-radius: 8px; margin-bottom: 16px; border-left: 4px solid; font-size: 0.875rem; }
.alert-success { background: #ECFDF5; color: #065F46; border-left-color: #10B981; }
.alert-error   { background: #FEF2F2; color: #991B1B; border-left-color: #EF4444; }
</style>
<div class="koha-page">
    <div class="koha-header">
        <h1>Koha ILS Integration</h1>
        <p>Connect to an external Koha library system to sync bibliographic records and circulation data.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    {{-- Configuration Card --}}
    <div class="k-card">
        <div class="k-card-header">
            <h3>Connection Settings</h3>
            @if($enabled)
                <span class="k-badge k-badge-success">Enabled</span>
            @else
                <span class="k-badge k-badge-off">Disabled</span>
            @endif
        </div>
        <div class="k-card-body">
            <form method="POST" action="{{ route('admin.koha.config') }}">
                @csrf
                <div class="k-field">
                    <label class="k-label" for="koha_base_url">Koha Base URL</label>
                    <input id="koha_base_url" class="k-input" type="url" name="koha_base_url"
                           value="{{ old('koha_base_url', $baseUrl) }}"
                           placeholder="https://koha.yourlibrary.org">
                </div>
                <div class="k-field">
                    <label class="k-label" for="koha_api_key">API Key / Credentials</label>
                    <input id="koha_api_key" class="k-input" type="password" name="koha_api_key"
                           value="{{ old('koha_api_key', $apiKey) }}"
                           placeholder="user:password  or  API token">
                    <small style="color:#6b7280; font-size:0.76rem; margin-top:4px; display:block;">
                        For HTTP Basic auth enter <code>username:password</code>. For token-based auth enter the token string.
                    </small>
                </div>
                <div class="k-field k-toggle">
                    <input type="checkbox" id="koha_enabled" name="koha_enabled" value="1" {{ $enabled ? 'checked' : '' }}>
                    <label for="koha_enabled" style="font-size:0.875rem; font-weight:600; cursor:pointer;">Enable Koha integration</label>
                </div>
                <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:8px;">
                    <button type="submit" class="k-btn k-btn-primary">Save Configuration</button>
                    <button type="button" class="k-btn k-btn-outline" id="testConnBtn" onclick="testKohaConnection()">Test Connection</button>
                </div>
            </form>
            <div id="testResult" style="margin-top:10px; display:none;"></div>
        </div>
    </div>

    {{-- Sync Actions Card --}}
    <div class="k-card">
        <div class="k-card-header">
            <h3>Manual Sync</h3>
        </div>
        <div class="k-card-body">
            <p style="font-size:0.875rem; color:#6b7280; margin:0 0 16px;">
                Manually trigger data sync from Koha. Koha integration must be enabled and properly configured.
            </p>
            <div class="k-sync-row">
                <form method="POST" action="{{ route('admin.koha.sync-books') }}">
                    @csrf
                    <button type="submit" class="k-btn k-btn-gold" {{ !$enabled ? 'disabled title=Enable Koha first' : '' }}>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.21"/></svg>
                        Sync Books (Bibliographic)
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.koha.sync-circulation') }}">
                    @csrf
                    <button type="submit" class="k-btn k-btn-gold" {{ !$enabled ? 'disabled title=Enable Koha first' : '' }}>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.21"/></svg>
                        Sync Circulation
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Last Sync Log --}}
    <div class="k-card">
        <div class="k-card-header">
            <h3>Last Sync Log</h3>
        </div>
        <div class="k-card-body">
            @if($lastLog)
                <div class="k-log">
                    <div style="margin-bottom:6px;">
                        <span class="k-log-key">Action:</span> {{ $lastLog->action }}&nbsp;&nbsp;
                        <span class="k-log-key">Status:</span>
                        <span class="k-badge {{ $lastLog->status === 'success' ? 'k-badge-success' : 'k-badge-error' }}">{{ $lastLog->status }}</span>
                    </div>
                    <div style="margin-bottom:6px;">
                        <span class="k-log-key">Records Synced:</span> {{ $lastLog->records_synced }}
                    </div>
                    @if($lastLog->message)
                        <div style="margin-bottom:6px;">
                            <span class="k-log-key">Message:</span> {{ $lastLog->message }}
                        </div>
                    @endif
                    <div>
                        <span class="k-log-key">Time:</span> {{ $lastLog->created_at->format('Y-m-d H:i:s') }}
                    </div>
                </div>
            @else
                <p style="color:#6b7280; font-size:0.875rem; margin:0;">No sync operations have been run yet.</p>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function testKohaConnection() {
    const btn = document.getElementById('testConnBtn');
    const result = document.getElementById('testResult');
    btn.disabled = true;
    btn.textContent = 'Testing…';
    result.style.display = 'none';

    fetch('{{ route("admin.koha.test") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({}),
    })
    .then(r => r.json())
    .then(data => {
        result.style.display = 'block';
        result.innerHTML = `<div class="alert ${data.success ? 'alert-success' : 'alert-error'}">${data.message}</div>`;
    })
    .catch(err => {
        result.style.display = 'block';
        result.innerHTML = `<div class="alert alert-error">Request failed: ${err.message}</div>`;
    })
    .finally(() => {
        btn.disabled = false;
        btn.textContent = 'Test Connection';
    });
}
</script>
@endpush
