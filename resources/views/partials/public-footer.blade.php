{{-- Shared public footer (homepage + guest catalog) --}}
@php
    $onHome  = request()->is('/');
    $anchor  = fn ($id) => $onHome ? "#{$id}" : url('/') . "#{$id}";
@endphp
<style>
    .pub-footer {
        background: #0f0f0f;
        color: rgba(255,255,255,0.6);
        font-family: 'Inter', system-ui, sans-serif;
        font-size: 0.85rem;
        padding: 56px 0 0;
        border-top: 3px solid #FFC72C;
    }
    .pub-footer-container { max-width: 1320px; margin: 0 auto; padding: 0 40px; }
    .pub-footer-grid { display: grid; grid-template-columns: 1.4fr 1fr 1fr 1fr; gap: 40px; }
    .pub-footer-brand .brand-name {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 1.2rem;
        font-weight: 700;
        color: #fff;
        margin: 0 0 14px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .pub-footer-brand .brand-name img { width: 34px; height: 34px; object-fit: contain; }
    .pub-footer-brand p { color: rgba(255,255,255,0.42); font-size: 0.83rem; line-height: 1.75; margin: 0; }
    .pub-footer h6 {
        color: #FFC72C;
        font-weight: 700;
        font-size: 0.78rem;
        margin: 0 0 18px;
        text-transform: uppercase;
        letter-spacing: 0.1em;
    }
    .pub-footer-links { display: flex; flex-direction: column; gap: 10px; }
    .pub-footer-links a { color: rgba(255,255,255,0.5); text-decoration: none; transition: color 0.2s, transform 0.2s; display: inline-block; }
    .pub-footer-links a:hover { color: #FFC72C; transform: translateX(4px); }
    .pub-footer-contact { display: flex; align-items: flex-start; gap: 10px; color: rgba(255,255,255,0.5); margin-bottom: 12px; overflow-wrap: anywhere; }
    .pub-footer-contact svg { flex-shrink: 0; margin-top: 2px; opacity: 0.6; }
    .pub-footer-bottom {
        border-top: 1px solid rgba(255,255,255,0.07);
        padding: 22px 0;
        margin-top: 44px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        color: rgba(255,255,255,0.32);
        font-size: 0.78rem;
    }
    @media (max-width: 1100px) { .pub-footer-grid { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 768px) {
        .pub-footer { padding-top: 40px; }
        .pub-footer-container { padding: 0 20px; }
        .pub-footer-grid { grid-template-columns: 1fr; gap: 28px; }
        .pub-footer-bottom { flex-direction: column; text-align: center; margin-top: 32px; }
    }
</style>

<footer class="pub-footer">
    <div class="pub-footer-container">
        <div class="pub-footer-grid">
            <div class="pub-footer-brand">
                <span class="brand-name">
                    <img src="{{ asset('images/pup-logo.png') }}" alt="" onerror="this.style.display='none'">
                    PUPSJ Libris Nexus
                </span>
                <p>The official digital library management system of the Polytechnic University of the Philippines — San Juan Campus. Empowering students and faculty with seamless access to knowledge.</p>
            </div>
            <div>
                <h6>Quick Links</h6>
                <div class="pub-footer-links">
                    <a href="{{ $onHome ? '#home' : url('/') }}">Home</a>
                    <a href="{{ $anchor('services') }}">Library Services</a>
                    <a href="{{ $anchor('how-it-works') }}">How It Works</a>
                    <a href="{{ route('login.selection') }}">Login</a>
                </div>
            </div>
            <div>
                <h6>Resources</h6>
                <div class="pub-footer-links">
                    <a href="{{ route('guest.books') }}">Book Catalog</a>
                    <a href="{{ route('guest.books', ['new_acquisition' => 1]) }}">New Acquisitions</a>
                    <a href="{{ route('login.selection') }}">Student &amp; Faculty Portal</a>
                </div>
            </div>
            <div>
                <h6>Contact</h6>
                <div class="pub-footer-contact">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <span>PUP San Juan Campus<br>San Juan, Metro Manila</span>
                </div>
                <div class="pub-footer-contact">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    <span>pupsjlibrisnexus@gmail.com</span>
                </div>
            </div>
        </div>
        <div class="pub-footer-bottom">
            <span>&copy; {{ date('Y') }} PUPSJ Libris Nexus. All rights reserved.</span>
            <span>Polytechnic University of the Philippines — San Juan</span>
        </div>
    </div>
</footer>
