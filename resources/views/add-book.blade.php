@extends('layouts.admin')

@section('content')
<div class="add-book-page">
    <h2>Add Book</h2>
    <p class="page-subtitle">Add a new book to the library catalog.</p>

    @if($errors->any())
        <div class="alert-error">
            <ul>
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h4>Book Information</h4>
            <span class="card-badge">New entry</span>
        </div>
        
        <div class="card-body">
            <form method="POST" action="{{ route('admin.books.store') }}" enctype="multipart/form-data" id="bookForm">
                @csrf
                
                <div class="form-grid">
                    <!-- 1. Title -->
                    <div class="form-group form-group-full">
                        <label>Title <span class="required">*</span></label>
                        <input type="text" name="title" id="titleInput" placeholder="e.g., Introduction to Computing" required value="{{ old('title') }}">
                    </div>

                    <!-- 2. Author -->
                    <div class="form-group form-group-full">
                        <label>Author <span class="required">*</span></label>
                        <input type="text" name="author" id="authorInput" placeholder="e.g., Author name" required value="{{ old('author') }}">
                        <small class="field-hint">If multiple authors, separate with commas</small>
                    </div>

                    <!-- 3. ISBN/ISSN -->
                    <div class="form-group form-group-full" id="isbnSection">
                        <label>ISBN/ISSN <small style="font-weight:400; color:#6b7280;">(optional — scan or enter for auto-fill)</small></label>
                        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                            <input type="text" name="isbn" id="isbnInput" placeholder="e.g., 9789712703688" value="{{ old('isbn') }}" style="flex: 1;">
                            <button type="button" id="fetchBookBtn" class="btn-fetch">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="10" cy="10" r="7"/>
                                    <line x1="21" y1="21" x2="15" y2="15"/>
                                </svg>
                                Search ISBN
                            </button>
                        </div>
                        <small class="field-hint">Enter ISBN and click Search to auto-fill book details</small>
                        <div id="isbnLoading" style="display: none; margin-top: 8px; color: #800000; font-size: 0.75rem;">
                            Searching for book information...
                        </div>
                        <div id="isbnError" style="display: none; margin-top: 8px; color: #dc2626; font-size: 0.75rem;"></div>
                    </div>

                    <!-- 3b. Accession Number (Optional) -->
                    <div class="form-group form-group-full">
                        <label>Accession Number <small style="font-weight:400; color:#6b7280;">(optional)</small></label>
                        <input type="text" name="accession_number" id="accessionNumberInput" placeholder="e.g., ACC-2024-001 or 001234" value="{{ old('accession_number') }}">
                        <small class="field-hint">Accession number assigned to this physical copy (optional)</small>
                    </div>

                    <!-- Cover Page Image Upload -->
                    <div class="form-group form-group-full">
                        <label>Book Cover Page <small style="font-weight:400; color:#6b7280;">(optional)</small></label>
                        <div class="image-upload-box" id="coverUploadBox">
                            <input type="file" name="cover_image" id="coverImageInput" accept="image/*" onchange="previewCoverImage(this)">
                            <div class="image-preview-area" id="coverPreviewArea" style="display:none; margin-top:12px;">
                                <div class="preview-card">
                                    <img id="coverPreviewImg" src="#" alt="Cover Preview" style="max-height:180px; border-radius:8px; border:1px solid #e5e7eb;">
                                    <button type="button" class="btn-remove-preview" onclick="removeCoverPreview()">Remove Cover</button>
                                </div>
                            </div>
                        </div>
                        <small class="field-hint">Upload front cover image of the book (displayed across catalog and book details)</small>
                    </div>

                    <!-- 4. Publisher -->
                    <div class="form-group">
                        <label>Publisher</label>
                        <input type="text" name="publisher" id="publisherInput" placeholder="Publisher name" value="{{ old('publisher') }}">
                    </div>

                    <!-- 5. Publication Year -->
                    <div class="form-group">
                        <label>Publication Year <span class="required">*</span></label>
                        <input type="number" name="publication_year" id="publicationYearInput" placeholder="e.g., 2024" min="1800" max="{{ date('Y') }}" value="{{ old('publication_year') }}" required>
                    </div>

                    <!-- 7. Collection -->
                    <div class="form-group">
                        <label>Collection <span class="required">*</span></label>
                        <input type="hidden" name="collection" id="collectionName" value="{{ old('collection') }}">
                        <select name="collection_type_id" id="collection" required
                                onchange="document.getElementById('collectionName').value=this.options[this.selectedIndex].dataset.name||this.options[this.selectedIndex].text; toggleLocField();">
                            <option value="">Select Collection</option>
                            @foreach($collectionTypes ?? [] as $ct)
                                <option value="{{ $ct->id }}"
                                    data-name="{{ $ct->name }}"
                                    data-has-loc="{{ $ct->has_loc_classification ? 'true' : 'false' }}"
                                    data-has-research="{{ $ct->has_research_type ? 'true' : 'false' }}"
                                    {{ old('collection_type_id') == $ct->id ? 'selected' : '' }}>
                                    {{ $ct->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="field-hint">This determines where the book is shelved</small>
                    </div>

                    <!-- 8. Research Type (Research and Innovation only) -->
                    <div class="form-group" id="researchTypeField" style="display:none;">
                        <label>Research Type <span class="required">*</span></label>
                        <select name="research_type" id="researchTypeSelect">
                            <option value="">Select Type</option>
                            <option value="capstone" {{ old('research_type') == 'capstone' ? 'selected' : '' }}>Capstone</option>
                            <option value="thesis"   {{ old('research_type') == 'thesis'   ? 'selected' : '' }}>Thesis</option>
                        </select>
                        <small class="field-hint">Classify the type of research work</small>
                    </div>

                    <!-- 8b. Program/Course (Research and Innovation only) -->
                    <div class="form-group" id="thesisCourseField" style="display:none;">
                        <label>Program / Course</label>
                        <select name="course_id" id="thesisCourseSelect">
                            <option value="">Select Program</option>
                            @foreach($courses ?? [] as $course)
                                <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                    {{ $course->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="field-hint">Select the program this research belongs to</small>
                    </div>

                    <!-- 9. A-Z Classification -->
                    <div class="form-group loc-field" id="locField">
                        <label>A-Z Classification</label>
                        <input type="text" name="loc_number" id="locNumber" placeholder="Select or enter classification" value="{{ old('loc_number') }}">
                        <small class="field-hint">
                            <strong>Format:</strong> Class&nbsp;SubClass&nbsp;Cutter&nbsp;Year — e.g., <code>QA76.73 .P98 2024</code>
                        </small>
                        <input type="text" id="locSearch" class="loc-search" placeholder="Filter classifications...">
                        <div class="loc-options" id="locOptions">
                            <div class="loc-option" data-code="A">A - General Works</div>
                            <div class="loc-option" data-code="B">B - Philosophy, Psychology, Religion</div>
                            <div class="loc-option" data-code="C">C - Auxiliary Sciences of History</div>
                            <div class="loc-option" data-code="D">D - World History and History of Europe, Asia, Africa</div>
                            <div class="loc-option" data-code="E-F">E-F - History of the Americas</div>
                            <div class="loc-option" data-code="G">G - Geography, Anthropology, Recreation</div>
                            <div class="loc-option" data-code="H">H - Social Sciences</div>
                            <div class="loc-option" data-code="J">J - Political Science</div>
                            <div class="loc-option" data-code="K">K - Law</div>
                            <div class="loc-option" data-code="L">L - Education</div>
                            <div class="loc-option" data-code="M">M - Music and Books on Music</div>
                            <div class="loc-option" data-code="N">N - Fine Arts</div>
                            <div class="loc-option" data-code="P">P - Language and Literature</div>
                            <div class="loc-option" data-code="Q">Q - Science</div>
                            <div class="loc-option" data-code="R">R - Medicine</div>
                            <div class="loc-option" data-code="S">S - Agriculture</div>
                            <div class="loc-option" data-code="T">T - Technology</div>
                            <div class="loc-option" data-code="U">U - Military Science</div>
                            <div class="loc-option" data-code="V">V - Naval Science</div>
                            <div class="loc-option" data-code="Z">Z - Bibliography, Library Science</div>
                        </div>
                    </div>

                    <!-- 11. Shelf Location -->
                    <div class="form-group">
                        <label>Shelf Location</label>
                        <input type="text" name="shelf_location" placeholder="e.g. Aisle 4, Shelf B-12" value="{{ old('shelf_location') }}">
                        <small class="field-hint">Physical location of the book</small>
                    </div>

                    <!-- 12. QR Code / Barcode -->
                    <div class="form-group">
                        <label>QR Code / Barcode</label>
                        <input type="text" name="barcode" id="barcodeInput" placeholder="QR Code number" value="{{ old('barcode') }}">
                        <small class="field-hint">Unique QR code for this book</small>
                    </div>

                    <!-- 13. Subject/Keywords -->
                    <div class="form-group form-group-full">
                        <label>Subject/Keywords</label>
                        <textarea name="subject" id="subjectInput" rows="3" placeholder="e.g., Mathematics, Programming, Databases">{{ old('subject') }}</textarea>
                        <small class="field-hint">Enter main subject (e.g., Mathematics)</small>
                    </div>

                    <!-- 14. Sub-Keywords -->
                    <div class="form-group form-group-full">
                        <label>Sub-Keywords (Optional)</label>
                        <textarea name="keywords" id="keywordsInput" rows="3" placeholder="e.g., Logic, Financial Math, Algebra, Calculus">{{ old('keywords') }}</textarea>
                        <small class="field-hint">Separate keywords with commas</small>
                    </div>

                    <!-- 15. Table of Contents Images -->
                    <div class="form-group form-group-full">
                        <label>Table of Contents Images <small style="font-weight:400; color:#6b7280;">(upload multiple pages)</small></label>
                        <div class="toc-upload-container">
                            <input type="file" name="toc_images[]" id="tocImagesInput" multiple accept="image/*" onchange="handleTocFiles(this)">
                            <div id="tocPreviewsGrid" class="toc-previews-grid" style="display:flex; flex-wrap:wrap; gap:12px; margin-top:12px;"></div>
                        </div>
                        <small class="field-hint">Pick multiple images or add additional pages for Table of Contents</small>
                    </div>

                    <!-- 16. New Acquisition / Donation -->
                    <div class="form-group">
                        <label>Acquisition Flags</label>
                        <div class="checkbox-group">
                            <input type="checkbox" name="is_new_acquisition" value="1" id="newAcq" {{ old('is_new_acquisition') ? 'checked' : '' }}>
                            <label for="newAcq">New Acquisition — recently added to collection</label>
                        </div>
                        <small class="field-hint">Check if this book is a recent addition to the collection</small>
                    </div>

                    <!-- 17. Filipiniana Criteria (shown only for Filipiniana collection) -->
                    <div class="form-group" id="filipinianaFields" style="display:none;">
                        <label>Filipiniana Criteria</label>
                        <small class="field-hint" style="display:block; margin-bottom:8px; color:#666;">Select the criteria that qualifies this book as Filipiniana:</small>
                        <div class="checkbox-group">
                            <input type="checkbox" name="is_filipino_author" value="1" id="isFilAuthor" {{ old('is_filipino_author') ? 'checked' : '' }}>
                            <label for="isFilAuthor">Written by a Filipino author</label>
                        </div>
                        <div class="checkbox-group" style="margin-top:6px;">
                            <input type="checkbox" name="is_ph_published" value="1" id="isPhPub" {{ old('is_ph_published') ? 'checked' : '' }}>
                            <label for="isPhPub">Published in the Philippines</label>
                        </div>
                        <div class="checkbox-group" style="margin-top:6px;">
                            <input type="checkbox" name="is_ph_subject" value="1" id="isPhSubj" {{ old('is_ph_subject') ? 'checked' : '' }}>
                            <label for="isPhSubj">About the Philippines (subject/content)</label>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                        Add Book
                    </button>
                    <a href="{{ route('admin.books') }}" class="btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* PUP Theme Variables */
.add-book-page {
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
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 1rem;
}

/* Page Header */
.add-book-page h2 {
    margin: 0 0 8px 0;
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--pup-maroon);
    letter-spacing: -0.025em;
    word-break: break-word;
}

.add-book-page .page-subtitle {
    color: var(--text-muted);
    font-size: 0.9375rem;
    margin-bottom: 1.75rem;
}

/* Alert Error */
.alert-error {
    background: #FEF2F2;
    color: #991B1B;
    padding: 1rem 1.25rem;
    border-radius: 10px;
    margin-bottom: 1.5rem;
    border-left: 4px solid #EF4444;
}

.alert-error ul {
    margin: 0;
    padding-left: 1.25rem;
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

/* Form Grid */
.form-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
}

.form-group {
    margin-bottom: 0;
}

.form-group-full {
    grid-column: span 3;
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

/* Fetch Button */
.btn-fetch {
    background: var(--pup-maroon);
    color: white;
    border: none;
    padding: 0.625rem 1.25rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    min-height: 44px;
    white-space: nowrap;
}

.btn-fetch:hover {
    background: var(--pup-maroon-dark);
    transform: translateY(-1px);
}

.btn-fetch:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* Checkbox Group */
.checkbox-group {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-top: 0.5rem;
    flex-wrap: wrap;
}

.checkbox-group input {
    width: auto;
    margin: 0;
    min-height: auto;
}

.checkbox-group label {
    margin-bottom: 0;
    font-weight: normal;
}

/* Loc Field */
.loc-field {
    display: none;
}

.loc-field.visible {
    display: block;
}

.loc-search {
    width: 100%;
    padding: 0.625rem 0.875rem;
    margin: 0.75rem 0;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: 0.8125rem;
    min-height: 42px;
}

.loc-search:focus {
    outline: none;
    border-color: var(--pup-maroon);
    box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.1);
}

.loc-options {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.5rem;
    margin-top: 0.5rem;
    max-height: 250px;
    overflow-y: auto;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 0.75rem;
    background: var(--bg-main);
}

.loc-option {
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    border-radius: 6px;
    transition: all 0.2s;
    font-size: 0.8125rem;
    color: var(--text);
    min-height: 40px;
    display: flex;
    align-items: center;
}

.loc-option:hover {
    background: #E5E7EB;
}

.loc-option.selected {
    background: var(--pup-maroon);
    color: white;
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-start;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border);
    flex-wrap: wrap;
}

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
    min-height: 48px;
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
    min-height: 48px;
}

.btn-outline:hover {
    background: var(--pup-maroon);
    color: white;
    transform: translateY(-2px);
}

/* Auto-filled field highlight */
.auto-filled {
    animation: highlightPulse 0.5s ease-in-out;
}

@keyframes highlightPulse {
    0% { background-color: #fff3cd; border-color: #FFC72C; }
    100% { background-color: white; border-color: var(--border); }
}

/* ============================================ */
/* RESPONSIVE DESIGN */
/* ============================================ */

@media (max-width: 1024px) {
    .add-book-page {
        padding: 0 0.75rem;
    }
    
    .form-grid {
        gap: 1.25rem;
    }
}

@media (max-width: 768px) {
    .add-book-page {
        padding: 0 0.5rem;
    }
    
    .add-book-page h2 {
        font-size: 1.5rem;
    }
    
    .page-subtitle {
        font-size: 0.875rem;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .form-group-full {
        grid-column: span 1;
    }
    
    .card-body {
        padding: 1rem;
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
    
    .loc-options {
        grid-template-columns: 1fr;
        max-height: 200px;
    }
    
    .loc-option {
        padding: 0.5rem;
        font-size: 0.75rem;
    }
    
    .form-actions {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .btn-primary,
    .btn-outline {
        justify-content: center;
        width: 100%;
    }
    
    .checkbox-group {
        flex-wrap: wrap;
    }
    
    .alert-error {
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
    }
    
    .alert-error ul {
        padding-left: 1rem;
    }
    
    div[style*="display: flex"] {
        flex-direction: column !important;
    }
    
    .btn-fetch {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .add-book-page h2 {
        font-size: 1.3rem;
    }
    
    .card-body {
        padding: 0.75rem;
    }
    
    .form-group label {
        font-size: 0.8rem;
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 0.5rem 0.75rem;
        font-size: 0.8rem;
        min-height: 40px;
    }
    
    .field-hint {
        font-size: 0.65rem;
    }
    
    .loc-search {
        padding: 0.5rem 0.75rem;
        font-size: 0.75rem;
        min-height: 38px;
    }
    
    .loc-option {
        padding: 0.4rem 0.6rem;
        font-size: 0.7rem;
        min-height: 36px;
    }
    
    .btn-primary,
    .btn-outline,
    .btn-fetch {
        padding: 0.6rem 1rem;
        font-size: 0.8rem;
        min-height: 42px;
    }
    
    .btn-primary svg,
    .btn-outline svg,
    .btn-fetch svg {
        width: 16px;
        height: 16px;
    }
    
    .checkbox-group label {
        font-size: 0.8rem;
    }
}

/* Landscape mode optimization */
@media (max-width: 768px) and (orientation: landscape) {
    .form-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }
    
    .form-group-full {
        grid-column: span 2;
    }
    
    .card-body {
        padding: 0.75rem;
    }
    
    .loc-options {
        grid-template-columns: repeat(2, 1fr);
        max-height: 150px;
    }
}
</style>

<script>
    // ============================================
    // ISBN AUTO-FILL FUNCTIONALITY
    // ============================================
    
    const fetchBtn = document.getElementById('fetchBookBtn');
    const isbnInput = document.getElementById('isbnInput');
    const loadingDiv = document.getElementById('isbnLoading');
    const errorDiv = document.getElementById('isbnError');
    
    // Form fields to auto-fill
    const titleInput = document.getElementById('titleInput');
    const authorInput = document.getElementById('authorInput');
    const publisherInput = document.getElementById('publisherInput');
    const publicationYearInput = document.getElementById('publicationYearInput');
    const subjectInput = document.getElementById('subjectInput');
    const keywordsInput = document.getElementById('keywordsInput');
    const collectionSelect = document.getElementById('collection');
    
    function highlightField(field) {
        if (!field) return;
        field.classList.add('auto-filled');
        setTimeout(() => {
            field.classList.remove('auto-filled');
        }, 500);
    }
    
    function setFieldValue(field, value) {
        if (field && value) {
            const oldValue = field.value;
            if (oldValue !== value) {
                field.value = value;
                highlightField(field);
                const event = new Event('change', { bubbles: true });
                field.dispatchEvent(event);
            }
        }
    }
    
    function setSelectValue(selectElement, value) {
        if (selectElement && value) {
            for (let i = 0; i < selectElement.options.length; i++) {
                if (selectElement.options[i].value === value) {
                    selectElement.selectedIndex = i;
                    highlightField(selectElement);
                    const event = new Event('change', { bubbles: true });
                    selectElement.dispatchEvent(event);
                    break;
                }
            }
        }
    }
    
    function clearError() {
        errorDiv.style.display = 'none';
        errorDiv.innerHTML = '';
    }
    
    function showError(message) {
        errorDiv.innerHTML = message;
        errorDiv.style.display = 'block';
        setTimeout(() => {
            if (errorDiv.style.display === 'block') {
                errorDiv.style.display = 'none';
            }
        }, 5000);
    }
    
    async function fetchBookByIsbn() {
        let isbn = isbnInput.value.trim();
        
        clearError();
        
        if (!isbn) {
            showError('Please enter an ISBN number.');
            isbnInput.focus();
            return;
        }
        
        isbn = isbn.replace(/[^0-9X]/gi, '');
        
        if (isbn.length < 10) {
            showError('Invalid ISBN format. Please enter at least 10 digits.');
            isbnInput.focus();
            return;
        }
        
        fetchBtn.disabled = true;
        loadingDiv.style.display = 'block';
        fetchBtn.innerHTML = `
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="2" x2="12" y2="6"/>
                <line x1="12" y1="18" x2="12" y2="22"/>
                <line x1="2" y1="12" x2="6" y2="12"/>
                <line x1="18" y1="12" x2="22" y2="12"/>
            </svg>
            Searching...
        `;
        
        try {
            // FIX: Use the correct admin-prefixed route
            console.log('Fetching ISBN:', isbn);
const response = await fetch(`/admin/books/fetch-by-isbn/${encodeURIComponent(isbn)}`);
console.log('Response status:', response.status);
            const result = await response.json();
            
            if (result.success && result.data) {
                const data = result.data;
                
                setFieldValue(titleInput, data.title);
                setFieldValue(authorInput, data.author);
                setFieldValue(publisherInput, data.publisher);
                setFieldValue(publicationYearInput, data.publication_year);
                setFieldValue(subjectInput, data.subject);
                
                if (data.keywords && keywordsInput) {
                    setFieldValue(keywordsInput, data.keywords);
                }
                
                if (data.collection && collectionSelect) {
                    setSelectValue(collectionSelect, data.collection);
                }
                
                let autoFilledFields = [];
                if (data.title) autoFilledFields.push('Title');
                if (data.author) autoFilledFields.push('Author');
                if (data.publisher) autoFilledFields.push('Publisher');
                if (data.publication_year) autoFilledFields.push('Publication Year');
                if (data.subject) autoFilledFields.push('Subject');
                if (data.keywords) autoFilledFields.push('Sub-Keywords');
                if (data.collection) autoFilledFields.push('Collection');
                
                const successMsg = document.createElement('div');
                successMsg.style.cssText = 'margin-top: 8px; color: #10b981; font-size: 0.75rem;';
                successMsg.innerHTML = `✅ Book information loaded! Auto-filled: ${autoFilledFields.join(', ')}. Please review and edit if needed.`;
                loadingDiv.parentNode.insertBefore(successMsg, loadingDiv.nextSibling);
                setTimeout(() => successMsg.remove(), 4000);
                
            } else {
                showError(result.message || 'Book not found. Please check the ISBN or enter details manually.');
            }
        } catch (error) {
            console.error('Fetch error:', error);
            showError('Network error. Please check your connection and try again.');
        } finally {
            fetchBtn.disabled = false;
            loadingDiv.style.display = 'none';
            fetchBtn.innerHTML = `
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="10" cy="10" r="7"/>
                    <line x1="21" y1="21" x2="15" y2="15"/>
                </svg>
                Search ISBN
            `;
        }
    }
    
    if (fetchBtn) {
        fetchBtn.addEventListener('click', fetchBookByIsbn);
    }
    
    if (isbnInput) {
        isbnInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                fetchBookByIsbn();
            }
        });
    }
    
    // ============================================
    // LoC AND BARCODE FUNCTIONALITY
    // ============================================
    
    const locField = document.getElementById('locField');
    const locNumber = document.getElementById('locNumber');
    const locOptionsContainer = document.getElementById('locOptions');
    const locSearch = document.getElementById('locSearch');
    const barcodeInput = document.getElementById('barcodeInput');
    const locNumberInput = document.getElementById('locNumber');
    let allOptions = [];

    function toggleLocField() {
        const selectedOpt  = collectionSelect.options[collectionSelect.selectedIndex];
        const hasLoc       = selectedOpt?.dataset?.hasLoc      === 'true';
        const hasResearch  = selectedOpt?.dataset?.hasResearch === 'true';
        const selectedName = selectedOpt?.dataset?.name || '';
        const isFilipiniana = selectedName === 'Filipiniana';

        // LoC classification field
        if (locField) {
            if (hasLoc) { locField.classList.add('visible'); }
            else        { locField.classList.remove('visible'); if (locNumber) locNumber.value = ''; }
        }

        // Filipiniana criteria checkboxes
        const filipinianaFields = document.getElementById('filipinianaFields');
        if (filipinianaFields) filipinianaFields.style.display = isFilipiniana ? '' : 'none';

        // Research Type + Program/Course
        const thesisField       = document.getElementById('thesisCourseField');
        const researchTypeField = document.getElementById('researchTypeField');
        const isbnSection       = document.getElementById('isbnSection');
        if (hasResearch) {
            if (thesisField)       thesisField.style.display = '';
            if (researchTypeField) researchTypeField.style.display = '';
            if (isbnSection)       isbnSection.style.display = 'none';
        } else {
            if (thesisField)       thesisField.style.display = 'none';
            if (researchTypeField) researchTypeField.style.display = 'none';
            if (isbnSection)       isbnSection.style.display = '';
        }
    }

    async function suggestBarcode() {
        const selectedOpt = collectionSelect.options[collectionSelect.selectedIndex];
        const collection = selectedOpt ? (selectedOpt.dataset.name || selectedOpt.text) : '';
        const locClass = locNumberInput ? locNumberInput.value : '';
        if (!collection) return;

        try {
            const res = await fetch(`/books/next-barcode?collection=${encodeURIComponent(collection)}&loc_class=${encodeURIComponent(locClass)}`);
            const data = await res.json();
            if (data.barcode) {
                barcodeInput.value = data.barcode;
            }
        } catch (err) {
            console.error('Barcode suggestion error:', err);
        }
    }

    if (collectionSelect) {
        collectionSelect.addEventListener('change', () => {
            toggleLocField();
            suggestBarcode();
        });
    }
    if (locNumberInput) {
        locNumberInput.addEventListener('input', suggestBarcode);
    }
    if (collectionSelect && collectionSelect.value) {
        suggestBarcode();
    }

    function filterLocOptions() {
        const query = locSearch.value.toLowerCase();
        Array.from(allOptions).forEach(opt => {
            const text = opt.textContent.toLowerCase();
            if (text.includes(query)) {
                opt.style.display = '';
            } else {
                opt.style.display = 'none';
            }
        });
    }

    function initLocOptions() {
        allOptions = Array.from(document.querySelectorAll('.loc-option'));
        allOptions.forEach(option => {
            option.addEventListener('click', function() {
                const code = this.dataset.code;
                allOptions.forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                if (locNumber) locNumber.value = code;
                if (locSearch) locSearch.value = '';
                filterLocOptions();
            });
        });
        if (locSearch) {
            locSearch.addEventListener('input', filterLocOptions);
        }
    }

    if (collectionSelect) {
        collectionSelect.addEventListener('change', toggleLocField);
        toggleLocField();
    }
    initLocOptions();

    if (locNumber) {
        locNumber.addEventListener('focus', () => {
            if (locOptionsContainer) locOptionsContainer.style.border = '1px solid var(--pup-maroon)';
        });
        locNumber.addEventListener('blur', () => {
            if (locOptionsContainer) locOptionsContainer.style.border = '1px solid var(--border)';
        });
    }

    // ─── Cover Image Preview ───
    window.previewCoverImage = function(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('coverPreviewImg').src = e.target.result;
                document.getElementById('coverPreviewArea').style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    };

    window.removeCoverPreview = function() {
        const input = document.getElementById('coverImageInput');
        if (input) input.value = '';
        document.getElementById('coverPreviewArea').style.display = 'none';
        document.getElementById('coverPreviewImg').src = '#';
    };

    // ─── Table of Contents Multi-Image Preview & Management ───
    let selectedTocFiles = [];

    window.handleTocFiles = function(input) {
        const grid = document.getElementById('tocPreviewsGrid');
        if (!grid) return;
        grid.innerHTML = '';
        selectedTocFiles = Array.from(input.files);

        selectedTocFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const card = document.createElement('div');
                card.className = 'toc-preview-card';
                card.style.cssText = 'position:relative; width:120px; height:150px; border:1.5px solid #e5e7eb; border-radius:8px; overflow:hidden; background:#fafafa; display:flex; flex-direction:column; align-items:center; justify-content:center; box-shadow:0 2px 6px rgba(0,0,0,0.05);';
                
                card.innerHTML = `
                    <img src="${e.target.result}" alt="TOC Page" style="width:100%; height:115px; object-fit:cover;">
                    <div style="font-size:0.7rem; color:#555; padding:3px 4px; text-align:center; width:100%; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        Page ${index + 1}
                    </div>
                    <button type="button" onclick="removeTocFile(${index})" style="position:absolute; top:4px; right:4px; background:rgba(220,38,38,0.9); color:#fff; border:none; border-radius:50%; width:22px; height:22px; font-size:12px; font-weight:bold; cursor:pointer; display:flex; align-items:center; justify-content:center;" title="Remove image">&times;</button>
                `;
                grid.appendChild(card);
            };
            reader.readAsDataURL(file);
        });
    };

    window.removeTocFile = function(index) {
        selectedTocFiles.splice(index, 1);
        const dt = new DataTransfer();
        selectedTocFiles.forEach(file => dt.items.add(file));
        const input = document.getElementById('tocImagesInput');
        input.files = dt.files;
        handleTocFiles(input);
    };
</script>
@endsection