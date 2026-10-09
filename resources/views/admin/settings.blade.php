@extends('layouts.admin')

@section('content')
<div style="max-width:600px; margin:60px auto; text-align:center;">
    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#800000" stroke-width="1.5" style="margin-bottom:16px;">
        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
    </svg>
    <h2 style="color:#800000; margin-bottom:8px;">Settings have moved</h2>
    <p style="color:#6b7280; margin-bottom:24px;">Penalty amounts and borrow day limits are now configured in Library Transactions.</p>
    <a href="{{ route('admin.operations.index') }}" style="background:#800000; color:white; padding:10px 24px; border-radius:8px; font-weight:600; text-decoration:none;">Go to Transactions</a>
</div>
@endsection
