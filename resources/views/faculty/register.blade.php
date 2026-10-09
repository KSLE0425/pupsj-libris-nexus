{{-- resources/views/faculty/register.blade.php --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Registration — PUPSJ Libris</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
<div class="split-card">

    {{-- Left: transparent, bg image shows through --}}
    <div class="hero-side"></div>

    {{-- Right: glass register panel --}}
    <div class="register-side">
        <div class="register-form-inner">

            <div style="display:flex;justify-content:center;margin-bottom:1rem;">
                <div class="seal-circle">
                    <svg width="34" height="34" viewBox="0 0 24 24" fill="#F5E642" xmlns="http://www.w3.org/2000/svg">
                        <path d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-9-2V7H4v3H1v2h3v3h2v-3h3v-2H6zm9 4c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg>
                </div>
            </div>

            <h1 class="login-title">Create Account</h1>
            <p class="login-subtitle">PUPSJ Libris Faculty Registration</p>

            @if($errors->any())
                <div class="error-box">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @if(session('success'))
                <div class="error-box" style="background:rgba(200,255,200,0.85);color:#1a6b1a;border-color:rgba(0,140,0,0.2);">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('faculty.register.post') }}" enctype="multipart/form-data" style="width:100%;">
                @csrf

                <p class="section-label">Personal Information</p>
                <div class="form-row">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" required placeholder="Juan">
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" required placeholder="Dela Cruz">
                    </div>
                </div>

                <p class="section-label">Employment Information</p>
                <div class="form-group">
                    <label>Employee ID</label>
                    <input type="text" name="employee_id" value="{{ old('employee_id') }}" required placeholder="2024-00123">
                    <span class="field-hint">Your official employee identification number</span>
                </div>

                <p class="section-label">Department</p>
                <div class="form-group">
                    @if($specialties->count() > 0)
                        <select name="specialty_id" required>
                            <option value="">Select Department</option>
                            @foreach($specialties as $specialty)
                                <option value="{{ $specialty->id }}" {{ old('specialty_id') == $specialty->id ? 'selected' : '' }}>
                                    {{ $specialty->name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="field-hint">Select your primary department</span>
                    @else
                        <p class="field-hint">No departments configured yet.</p>
                    @endif
                </div>

                <p class="section-label">Account & Verification</p>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="you@pup.edu.ph">
                    <span class="field-hint">You will receive approval notifications here</span>
                </div>

                <div class="form-group">
                    <label>Verification Document (optional)</label>
                    <input type="file" name="verification_doc" accept=".jpg,.jpeg,.png,.pdf">
                    <span class="field-hint">Upload your faculty ID or appointment document</span>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="faculty_password" name="password" required placeholder="••••••••">
                            <button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility('faculty_password', this)" title="Show Password" aria-label="Toggle password visibility">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="faculty_password_confirmation" name="password_confirmation" required placeholder="••••••••">
                            <button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility('faculty_password_confirmation', this)" title="Show Password" aria-label="Toggle password visibility">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="submit-btn">Create Account</button>
            </form>

            <p class="register-text">Already have an account? <a href="{{ route('faculty.login') }}">Login here</a></p>

        </div>
    </div>
</div>

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
</body>
</html>