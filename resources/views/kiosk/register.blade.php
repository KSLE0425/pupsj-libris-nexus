{{-- resources/views/kiosk/register.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — PUPSJ Libris Kiosk</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
<div class="split-card">

    <div class="hero-side"></div>

    {{-- Right: scrollable register panel --}}
    <div class="register-side">
        <div class="register-form-inner">

            <div style="display:flex; justify-content:center; margin-bottom:1rem;">
                <div class="seal-circle">
                    <svg width="34" height="34" viewBox="0 0 24 24" fill="#F5E642" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 4h5v8l-2.5-1.5L6 12V4z"/>
                    </svg>
                </div>
            </div>

            <h1 class="login-title">Create Account</h1>
            <p class="login-subtitle">Register to use the PUPSJ Library Kiosk</p>

            @if(session('kiosk_success'))
                <div style="display:flex;align-items:flex-start;gap:10px;background:#ecfdf5;border:1px solid #6ee7b7;border-left:4px solid #10b981;border-radius:10px;padding:13px 16px;margin-bottom:18px;width:100%;box-sizing:border-box;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.5" style="flex-shrink:0;margin-top:1px;"><polyline points="20 6 9 17 4 12"/></svg>
                    <div>
                        <p style="margin:0 0 2px;font-size:0.88rem;font-weight:700;color:#065f46;font-family:'Poppins',sans-serif;">Account Created!</p>
                        <p style="margin:0;font-size:0.82rem;color:#047857;font-family:'Poppins',sans-serif;">{{ session('kiosk_success') }}</p>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="error-box" id="formErrorBox">{{ $errors->first() }}</div>
            @endif

            {{-- User Type Toggle --}}
            <div style="display:flex; gap:8px; margin-bottom:4px;">
                <button type="button" id="btn-student" onclick="setType('student')"
                        style="flex:1; padding:10px; border-radius:8px; font-family:'Poppins',sans-serif;
                               font-size:0.88rem; font-weight:700; cursor:pointer; transition:all .2s;
                               background:#800000; color:#F5E642; border:2px solid #800000;">
                    Student
                </button>
                <button type="button" id="btn-faculty" onclick="setType('faculty')"
                        style="flex:1; padding:10px; border-radius:8px; font-family:'Poppins',sans-serif;
                               font-size:0.88rem; font-weight:700; cursor:pointer; transition:all .2s;
                               background:rgba(255,255,255,0.12); color:#fff; border:2px solid rgba(255,255,255,0.5);">
                    Faculty
                </button>
            </div>

            <form method="POST" action="{{ route('kiosk.register.post') }}" enctype="multipart/form-data" style="width:100%;">
                @csrf
                <input type="hidden" name="user_type" id="user_type" value="{{ old('user_type', 'student') }}">

                {{-- ── STUDENT FIELDS ── --}}
                <div id="student-fields">
                    <p class="section-label">Account Credentials</p>
                    <div class="form-group">
                        <label for="student_number">Student Number</label>
                        <input type="text" id="student_number" name="student_number"
                               value="{{ old('student_number') }}" placeholder="e.g. 2024-00001-SJ-0">
                    </div>
                    <div class="form-group">
                        <label for="s_email">Email Address</label>
                        <input type="email" id="s_email" name="email"
                               value="{{ old('email') }}" placeholder="you@example.com">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="s_password">Password</label>
                            <div class="password-wrapper">
                                <input type="password" id="s_password" name="password" placeholder="Min. 6 characters">
                                <button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility('s_password', this)" title="Show Password" aria-label="Toggle password visibility">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="s_password_confirmation">Confirm Password</label>
                            <div class="password-wrapper">
                                <input type="password" id="s_password_confirmation" name="password_confirmation" placeholder="Repeat password">
                                <button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility('s_password_confirmation', this)" title="Show Password" aria-label="Toggle password visibility">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <p class="section-label">Personal Information</p>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="s_first_name">First Name</label>
                            <input type="text" id="s_first_name" name="first_name" value="{{ old('first_name') }}">
                        </div>
                        <div class="form-group">
                            <label for="s_last_name">Last Name</label>
                            <input type="text" id="s_last_name" name="last_name" value="{{ old('last_name') }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="s_program">Program</label>
                            <select id="s_program" name="program">
                                <option value="">— Select program —</option>
                                @foreach($programs as $prog)
                                    <option value="{{ $prog->name }}" {{ old('program') === $prog->name ? 'selected' : '' }}>
                                        {{ $prog->code ? $prog->code.' – ' : '' }}{{ $prog->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="s_year_level">Year Level</label>
                            <select id="s_year_level" name="year_level">
                                <option value="">Select year</option>
                                @foreach([1,2,3,4] as $y)
                                    <option value="{{ $y }}" {{ old('year_level') == $y ? 'selected' : '' }}>Year {{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <p class="section-label">Verification Document</p>
                    <div class="form-group">
                        <label for="cor_file">Certificate of Registration (COR) <span style="font-weight:400; font-size:0.8rem; opacity:0.7;">(Optional)</span></label>
                        <input type="file" id="cor_file" name="cor_file" accept=".jpg,.jpeg,.png,.pdf">
                        <span class="field-hint">JPG, PNG, or PDF — max 2MB. Can be uploaded later in your profile.</span>
                    </div>
                </div>

                {{-- ── FACULTY FIELDS ── --}}
                <div id="faculty-fields" style="display:none;">
                    <p class="section-label">Account Credentials</p>
                    <div class="form-group">
                        <label for="employee_id">Employee ID</label>
                        <input type="text" id="employee_id" name="employee_id"
                               value="{{ old('employee_id') }}" placeholder="Enter your employee ID">
                    </div>
                    <div class="form-group">
                        <label for="f_email">Email Address</label>
                        <input type="email" id="f_email" name="faculty_email"
                               value="{{ old('faculty_email') }}" placeholder="you@example.com">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="f_password">Password</label>
                            <div class="password-wrapper">
                                <input type="password" id="f_password" name="faculty_password" placeholder="Min. 6 characters">
                                <button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility('f_password', this)" title="Show Password" aria-label="Toggle password visibility">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="f_password_confirmation">Confirm Password</label>
                            <div class="password-wrapper">
                                <input type="password" id="f_password_confirmation" name="faculty_password_confirmation" placeholder="Repeat password">
                                <button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility('f_password_confirmation', this)" title="Show Password" aria-label="Toggle password visibility">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <p class="section-label">Personal Information</p>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="f_first_name">First Name</label>
                            <input type="text" id="f_first_name" name="faculty_first_name" value="{{ old('faculty_first_name') }}">
                        </div>
                        <div class="form-group">
                            <label for="f_last_name">Last Name</label>
                            <input type="text" id="f_last_name" name="faculty_last_name" value="{{ old('faculty_last_name') }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Department <span style="font-weight:400;opacity:.6;">(optional)</span></label>
                        @php $oldIds = is_array(old('specialty_ids')) ? old('specialty_ids') : []; @endphp
                        @if($specialties->isEmpty())
                            <p style="font-size:0.8rem;color:rgba(255,255,255,0.55);margin-top:4px;">No departments configured yet.</p>
                        @else
                        <div style="display:flex; flex-wrap:wrap; gap:8px 16px; margin-top:4px;">
                            @foreach($specialties as $spec)
                            <label style="display:flex; align-items:center; gap:6px; font-size:0.82rem; font-weight:400; color:rgba(255,255,255,0.9); text-transform:none; letter-spacing:0; cursor:pointer;">
                                <input type="checkbox" name="specialty_ids[]" value="{{ $spec->id }}"
                                       {{ in_array($spec->id, $oldIds) ? 'checked' : '' }}
                                       style="width:14px; height:14px; accent-color:#FFC72C; cursor:pointer;">
                                {{ $spec->name }}
                            </label>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    <p class="section-label">Verification Document</p>
                    <div class="form-group">
                        <label for="verification_doc">Employment / Verification Document <span style="font-weight:400;opacity:.6;">(optional)</span></label>
                        <input type="file" id="verification_doc" name="verification_doc" accept=".jpg,.jpeg,.png,.pdf">
                        <span class="field-hint">JPG, PNG, or PDF — max 2MB</span>
                    </div>
                </div>

                <button type="submit" class="submit-btn" style="margin-top:10px;" id="submitBtn">Create Account</button>
            </form>

            <p class="register-text">
                Already have an account? <a href="{{ route('kiosk.sign-in') }}">Sign In</a>
            </p>
            <a href="{{ route('kiosk.index') }}" class="back-link">← Back to Kiosk Menu</a>

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

function setType(type) {
    document.getElementById('user_type').value = type;
    const isStudent = type === 'student';

    document.getElementById('student-fields').style.display = isStudent ? '' : 'none';
    document.getElementById('faculty-fields').style.display = isStudent ? 'none' : '';

    const btnS = document.getElementById('btn-student');
    const btnF = document.getElementById('btn-faculty');

    btnS.style.background   = isStudent ? '#800000' : 'rgba(255,255,255,0.12)';
    btnS.style.color        = '#fff';
    btnS.style.borderColor  = isStudent ? '#800000' : 'rgba(255,255,255,0.5)';

    btnF.style.background   = isStudent ? 'rgba(255,255,255,0.12)' : '#800000';
    btnF.style.color        = '#fff';
    btnF.style.borderColor  = isStudent ? 'rgba(255,255,255,0.5)' : '#800000';
}

// Restore on validation error
(function() {
    const saved = document.getElementById('user_type').value;
    if (saved === 'faculty') setType('faculty');
})();

// ── AJAX form submission (no-refresh on error) ──
(function() {
    const form = document.querySelector('form[action*="register"]');
    if (!form) return;

    // Error display helper
    function showError(msg) {
        let box = document.getElementById('formErrorBox');
        if (!box) {
            box = document.createElement('div');
            box.id = 'formErrorBox';
            box.className = 'error-box';
            form.parentNode.insertBefore(box, form);
        }
        box.textContent = msg;
        box.style.display = '';
        box.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    function hideError() {
        const box = document.getElementById('formErrorBox');
        if (box) box.style.display = 'none';
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        hideError();

        const submitBtn = form.querySelector('[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Creating account...';

        const formData = new FormData(form);

        const csrfToken = form.querySelector('input[name="_token"]')?.value || document.querySelector('meta[name="csrf-token"]')?.content || '';

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: formData,
        })
        .then(function(response) {
            if (response.ok) {
                // Success — reload or redirect to same page with success flash
                window.location.reload();
                return;
            }
            if (response.status === 419) {
                showError('Your session has expired. Please refresh the page and try submitting again.');
                return;
            }
            return response.json().then(function(data) {
                if (data.errors) {
                    const firstKey = Object.keys(data.errors)[0];
                    showError(data.errors[firstKey][0]);
                } else if (data.message) {
                    showError(data.message);
                } else {
                    showError('Please check your details. Some required fields may be missing or invalid.');
                }
            }).catch(function() {
                showError('Registration could not be completed. Please check your inputs.');
            });
        })
        .catch(function() {
            showError('A network error occurred. Please try again.');
        })
        .finally(function() {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        });
    });
})();
</script>
</body>
</html>
