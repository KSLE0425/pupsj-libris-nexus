{{-- resources/views/student/profile.blade.php --}}
@extends('layouts.student')

@section('title', 'My Profile')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    * {
        font-family: 'Nunito', sans-serif;
        box-sizing: border-box;
    }

    body {
        background-color: #f4f6fb;
        color: #1a1d2e;
    }

    /* ── Top Bar ── */
    .profile-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 28px;
    }

    .profile-topbar h4 {
        font-size: 22px;
        font-weight: 700;
        margin: 0 0 2px;
        color: #1a1d2e;
    }

    .profile-topbar .date {
        font-size: 13px;
        color: #9fa3b1;
        margin: 0;
    }

    .topbar-right {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .search-box {
        display: flex;
        align-items: center;
        background: #fff;
        border: 1px solid #e8eaf0;
        border-radius: 12px;
        padding: 8px 16px;
        gap: 8px;
        width: 220px;
    }

    .search-box input {
        border: none;
        outline: none;
        font-size: 14px;
        color: #9fa3b1;
        background: transparent;
        width: 100%;
    }

    .search-box svg {
        color: #9fa3b1;
        flex-shrink: 0;
    }

    .icon-btn {
        width: 40px;
        height: 40px;
        background: #fff;
        border: 1px solid #e8eaf0;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: #9fa3b1;
    }

    .avatar-sm {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        object-fit: cover;
    }

    .avatar-sm-placeholder {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: linear-gradient(135deg, #b70f0f, #800000);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 700;
        font-size: 14px;
    }

    /* ── Banner ── */
    .profile-banner {
        width: 100%;
        height: 140px;
        border-radius: 20px;
        background: linear-gradient(120deg, #800000 0%, #c80808 50%, #a10b0b 100%);
        margin-bottom: 0;
    }

    /* ── Profile Card ── */
    .profile-card {
        background: #fff;
        border-radius: 0 0 20px 20px;
        padding: 0 32px 28px;
        margin-bottom: 24px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    }

    .profile-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 16px;
    }

    .profile-avatar-wrap {
        display: flex;
        align-items: center;
        gap: 18px;
    }

    .profile-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        border: 4px solid #fff;
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
        object-fit: cover;
        margin-top: -30px;
    }

    .profile-avatar-placeholder {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        border: 4px solid #fff;
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
        background: linear-gradient(135deg, #b70f0f, #800000);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 700;
        font-size: 26px;
        margin-top: -30px;
        flex-shrink: 0;
    }

    .profile-name {
        font-size: 18px;
        font-weight: 700;
        margin: 0 0 3px;
        color: #1a1d2e;
    }

    .profile-email {
        font-size: 13px;
        color: #9fa3b1;
        margin: 0;
    }

    .btn-edit {
        background: #800000;
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 10px 26px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-edit:hover {
        background: #5a0000;
    }

    /* ── Form Section ── */
    .form-card {
        background: #fff;
        border-radius: 20px;
        padding: 28px 32px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        margin-bottom: 24px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px 28px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .form-group label {
        font-size: 13px;
        font-weight: 600;
        color: #1a1d2e;
    }

    .form-group input,
    .form-group select {
        background: #f7f8fc;
        border: 1px solid #f0f1f7;
        border-radius: 10px;
        padding: 12px 16px;
        font-size: 14px;
        color: #1a1d2e;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
        appearance: none;
        -webkit-appearance: none;
        font-family: 'Nunito', sans-serif;
    }

    .form-group input::placeholder,
    .form-group select::placeholder {
        color: #c0c4d4;
    }

    .form-group input:focus,
    .form-group select:focus {
        border-color: #800000;
        box-shadow: 0 0 0 3px rgba(128,0,0,0.1);
        background: #fff;
    }

    .form-group input.is-invalid,
    .form-group select.is-invalid {
        border-color: #e74c6e;
    }

    .invalid-feedback {
        font-size: 12px;
        color: #e74c6e;
        margin-top: 2px;
    }

    .select-wrapper {
        position: relative;
    }

    .select-wrapper::after {
        content: '';
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        width: 0;
        height: 0;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 5px solid #9fa3b1;
        pointer-events: none;
    }

    .select-wrapper select {
        width: 100%;
        padding-right: 36px;
    }

    /* ── Password section ── */
    .section-title {
        font-size: 15px;
        font-weight: 700;
        color: #1a1d2e;
        margin: 0 0 18px;
    }

    /* ── Email Section ── */
    .email-card {
        background: #fff;
        border-radius: 20px;
        padding: 28px 32px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        margin-bottom: 24px;
    }

    .email-item {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 16px;
    }

    .email-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: #800000;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .email-icon svg {
        color: #fff;
    }

    .email-text .address {
        font-size: 14px;
        font-weight: 600;
        color: #1a1d2e;
        margin: 0 0 2px;
    }

    .email-text .time {
        font-size: 12px;
        color: #9fa3b1;
        margin: 0;
    }

    .btn-add-email {
        background: #eef3fe;
        color: #800000;
        border: none;
        border-radius: 10px;
        padding: 10px 22px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        font-family: 'Nunito', sans-serif;
        transition: background 0.2s;
    }

    .btn-add-email:hover {
        background: #dbe8fd;
    }

    /* ── Save Button ── */
    .btn-save {
        background: #800000;
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 13px 0;
        width: 100%;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        font-family: 'Nunito', sans-serif;
        transition: background 0.2s;
        margin-top: 8px;
    }

    .btn-save:hover {
        background: #5a0000;
    }

    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
        .profile-banner {
            height: 100px;
        }
        .search-box {
            display: none;
        }
    }
    /* ── Password Toggle ── */
    .password-wrapper {
        position: relative;
        width: 100%;
        display: flex;
        align-items: center;
    }
    .password-wrapper input {
        padding-right: 48px !important;
    }
    .toggle-password-btn {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: #e8eaf0;
        border: 1px solid #d0d3de;
        border-radius: 8px;
        cursor: pointer;
        padding: 6px;
        color: #4a5568;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        z-index: 10;
        transition: all 0.2s ease;
        user-select: none;
        line-height: 1;
        width: 34px;
        height: 34px;
    }
    .toggle-password-btn:hover {
        color: #800000;
        background: #dfe3ec;
        border-color: #800000;
        transform: translateY(-50%) scale(1.05);
    }
    .toggle-password-btn svg {
        width: 18px;
        height: 18px;
        pointer-events: none;
    }

    /* ── MOBILE PROFILE ── */
    .form-grid > *, .form-group { min-width: 0; }
    .form-group input, .form-group select, .form-group textarea { width: 100%; max-width: 100%; }
    .profile-email, .email-text, .email-text * { overflow-wrap: anywhere; min-width: 0; }
    .email-item { min-width: 0; }
    @media (max-width: 768px) {
        .profile-topbar { margin-bottom: 14px; }
        .profile-topbar h4 { font-size: 1.05rem; }
        .profile-banner { height: 90px; border-radius: 16px 16px 0 0; }
        .profile-card { padding: 0 16px 18px; border-radius: 0 0 16px 16px; margin-bottom: 16px; }
        .profile-header-row { flex-direction: column; align-items: flex-start; gap: 12px; }
        .profile-avatar-wrap { gap: 12px; min-width: 0; width: 100%; }
        .profile-avatar-wrap > div:last-child { min-width: 0; }
        .profile-avatar, .profile-avatar-placeholder { width: 64px; height: 64px; font-size: 20px; margin-top: -26px; }
        .profile-name { font-size: 16px; }
        .btn-edit { width: 100%; padding: 10px; }
        .form-card, .email-card { padding: 18px 16px; border-radius: 16px; margin-bottom: 16px; }
        .email-item { flex-wrap: wrap; }
        .btn-save { width: 100%; }
    }
</style>
@endpush

@section('content')

{{-- Top Bar --}}
<div class="profile-topbar">
    <div>
        <h4>Welcome, {{ $student->first_name }}</h4>
        <p class="date">{{ now()->format('D, d F Y') }}</p>
    </div>
    <div class="topbar-right">
        <div class="avatar-sm-placeholder">
            {{ strtoupper(substr($student->first_name, 0, 1)) }}{{ strtoupper(substr($student->last_name, 0, 1)) }}
        </div>
    </div>
</div>

{{-- Banner + Profile Header --}}
<div class="profile-banner"></div>
<div class="profile-card">
    <div class="profile-header-row">
        <div class="profile-avatar-wrap">
            <div class="profile-avatar-placeholder">
                {{ strtoupper(substr($student->first_name, 0, 1)) }}{{ strtoupper(substr($student->last_name, 0, 1)) }}
            </div>
            <div>
                <p class="profile-name">{{ $student->first_name }} {{ $student->last_name }}</p>
                <p class="profile-email">{{ $student->email }}</p>
            </div>
        </div>
        <button class="btn-edit" type="button" onclick="document.getElementById('profileForm').scrollIntoView({behavior:'smooth'})">
            Edit
        </button>
    </div>
</div>

{{-- Profile Form --}}
<form method="POST" action="{{ route('student.profile.update') }}" id="profileForm">
    @csrf

    <div class="form-card">
        <p class="section-title">Personal Information</p>
        <div class="form-grid">
            <div class="form-group">
                <label>Student Number</label>
                <input type="text" value="{{ $student->student_number }}" readonly>
            </div>

            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name"
                       value="{{ old('name', trim($student->first_name . ' ' . $student->last_name)) }}"
                       placeholder="Your Full Name"
                       class="{{ $errors->has('name') || $errors->has('first_name') ? 'is-invalid' : '' }}">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label>Program</label>
                <input type="text" name="course"
                       value="{{ old('course', $student->program) }}"
                       placeholder="Your Course"
                       class="{{ $errors->has('course') ? 'is-invalid' : '' }}">
                @error('program')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label>Year Level</label>
                <div class="select-wrapper">
                    <select name="year_level" class="{{ $errors->has('year_level') ? 'is-invalid' : '' }}">
                        <option value="1" {{ old('year_level', $student->year_level) == '1' ? 'selected' : '' }}>1st Year</option>
                        <option value="2" {{ old('year_level', $student->year_level) == '2' ? 'selected' : '' }}>2nd Year</option>
                        <option value="3" {{ old('year_level', $student->year_level) == '3' ? 'selected' : '' }}>3rd Year</option>
                        <option value="4" {{ old('year_level', $student->year_level) == '4' ? 'selected' : '' }}>4th Year</option>
                    </select>
                </div>
                @error('year_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    {{-- Email Section --}}
    <div class="email-card">
        <p class="section-title">Email Address</p>

        <div class="email-item">
            <div class="email-icon">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                </svg>
            </div>
            <div class="email-text">
                <p class="address">{{ $student->email }}</p>
                <p class="time">{{ isset($student->email_verified_at) && $student->email_verified_at ? $student->email_verified_at->diffForHumans() : 'Not verified' }}</p>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 16px;">
            <label>Email Address</label>
            <input type="email" name="email"
                   value="{{ old('email', $student->email) }}"
                   placeholder="your@email.com"
                   class="{{ $errors->has('email') ? 'is-invalid' : '' }}">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-group" style="margin-bottom: 16px;">
            <label>PUP Email Address</label>
            <input type="email" name="pup_email"
                   value="{{ old('pup_email', $student->pup_email ?? '') }}"
                   placeholder="your@iskolarngbayan.pup.edu.ph"
                   class="{{ $errors->has('pup_email') ? 'is-invalid' : '' }}">
            @error('pup_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    {{-- Password Section --}}
    <div class="form-card">
        <p class="section-title">Change Password</p>
        <div class="form-grid">
            <div class="form-group">
                <label>New Password</label>
                <div class="password-wrapper">
                    <input type="password" id="profile_password" name="password"
                           placeholder="Leave blank to keep current"
                           class="{{ $errors->has('password') ? 'is-invalid' : '' }}">
                    <button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility('profile_password', this)" title="Show Password" aria-label="Toggle password visibility">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <div class="password-wrapper">
                    <input type="password" id="profile_password_confirmation" name="password_confirmation"
                           placeholder="Confirm new password">
                    <button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility('profile_password_confirmation', this)" title="Show Password" aria-label="Toggle password visibility">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <button type="submit" class="btn-save">Update Profile</button>
    </div>

</form>

<script>
function togglePasswordVisibility(fieldId, btn) {
    const input = document.getElementById(fieldId);
    if (!input) return;
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    btn.innerHTML = isPassword ? `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
            <line x1="1" y1="1" x2="23" y2="23"></line>
        </svg>
    ` : `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
            <circle cx="12" cy="12" r="3"></circle>
        </svg>
    `;
    btn.title = isPassword ? 'Hide Password' : 'Show Password';
}
</script>

@endsection