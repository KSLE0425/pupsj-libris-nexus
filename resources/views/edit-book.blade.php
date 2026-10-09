@extends('layouts.admin')

@section('content')
<div class="edit-book-page">
    <h2>Edit Book</h2>
    <p class="page-subtitle">Update book details and manage inventory information.</p>

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
            <span class="card-badge">Edit existing book</span>
        </div>
        
        <div class="card-body">
            <form method="POST" action="{{ route('admin.books.update', $book->id) }}" enctype="multipart/form-data" id="editBookForm">
                @csrf
                
                <div class="form-grid">
                    <!-- 1. Title -->
                    <div class="form-group form-group-full">
                        <label>Title <span class="required">*</span></label>
                        <input type="text" name="title" id="titleInput" value="{{ old('title', $book->title) }}" required>
                    </div>

                    <!-- 2. Author -->
                    <div class="form-group form-group-full">
                        <label>Author <span class="required">*</span></label>
                        <input type="text" name="author" id="authorInput" value="{{ old('author', $book->author) }}" required>
                        <small class="field-hint">If multiple authors, separate with commas</small>
                    </div>

                    <!-- 3. ISBN/ISSN -->
                    <div class="form-group form-group-full" id="isbnSection">
                        <label>ISBN/ISSN <small style="font-weight:400; color:#6b7280;">(optional)</small></label>
                        <input type="text" name="isbn" id="isbnInput" value="{{ old('isbn', $book->isbn) }}" placeholder="e.g., 9789712703688">
                        <small class="field-hint">Enter the ISBN or ISSN number</small>
                    </div>

                    <!-- 3b. Accession Number (Optional) -->
                    <div class="form-group form-group-full">
                        <label>Accession Number <small style="font-weight:400; color:#6b7280;">(optional)</small></label>
                        <input type="text" name="accession_number" id="accessionNumberInput" value="{{ old('accession_number', $book->accession_number) }}" placeholder="e.g., ACC-2024-001 or 001234">
                        <small class="field-hint">Accession number assigned to this physical copy (optional)</small>
                    </div>

                    <!-- Cover Page Image Upload -->
                    <div class="form-group form-group-full">
                        <label>Book Cover Page <small style="font-weight:400; color:#6b7280;">(optional)</small></label>
                        <div class="image-upload-box" id="coverUploadBox">
                            @if($book->title_cover_image_path)
                                <div style="display:flex; align-items:flex-start; gap:16px; margin-bottom:12px; background:#f9fafb; padding:12px; border-radius:10px; border:1px solid #e5e7eb;">
                                    <img src="{{ asset('storage/' . $book->title_cover_image_path) }}" alt="Current Cover" style="max-height:140px; border-radius:6px; box-shadow:0 2px 6px rgba(0,0,0,0.1);">
                                    <div>
                                        <div style="font-size:0.82rem; font-weight:700; color:#111; margin-bottom:4px;">Current Front Cover</div>
                                        <label style="display:inline-flex; align-items:center; gap:6px; font-size:0.8rem; color:#dc2626; cursor:pointer; font-weight:600;">
                                            <input type="checkbox" name="remove_cover" value="1" style="width:16px; height:16px; accent-color:#dc2626;">
                                            Remove current cover image
                                        </label>
                                    </div>
                                </div>
                            @endif
                            <input type="file" name="cover_image" id="coverImageInput" accept="image/*" onchange="previewCoverImage(this)">
                            <div class="image-preview-area" id="coverPreviewArea" style="display:none; margin-top:12px;">
                                <div class="preview-card">
                                    <img id="coverPreviewImg" src="#" alt="Cover Preview" style="max-height:160px; border-radius:8px; border:1px solid #e5e7eb;">
                                    <button type="button" class="btn-remove-preview" onclick="removeCoverPreview()" style="margin-top:6px; background:#dc2626; color:#fff; border:none; padding:4px 10px; border-radius:6px; font-size:0.75rem; cursor:pointer;">Cancel New Cover</button>
                                </div>
                            </div>
                        </div>
                        <small class="field-hint">Upload front cover image of the book</small>
                    </div>

                    <!-- 4. Publisher -->
                    <div class="form-group">
                        <label>Publisher</label>
                        <input type="text" name="publisher" id="publisherInput" value="{{ old('publisher', $book->publisher) }}" placeholder="Publisher name">
                    </div>

                    <!-- 5. Publication Year -->
                    <div class="form-group">
                        <label>Publication Year</label>
                        <input type="number" name="publication_year" id="publicationYearInput" value="{{ old('publication_year', $book->publication_year) }}" placeholder="e.g., 2024" min="1800" max="{{ date('Y') }}">
                    </div>

                    <!-- 6. Number of Copies -->
                    <div class="form-group">
                        <label>Number of Copies</label>
                        <input type="number" name="copies" value="{{ old('copies', $book->copies) }}" min="0" max="1000">
                        <small class="field-hint">Total physical copies available</small>
                    </div>

                    <!-- 7. Collection -->
                    <div class="form-group">
                        <label>Collection <span class="required">*</span></label>
                        <input type="hidden" name="collection" id="collectionName" value="{{ old('collection', $book->collection) }}">
                        <select name="collection_type_id" id="collection" required
                                onchange="document.getElementById('collectionName').value=this.options[this.selectedIndex].dataset.name||this.options[this.selectedIndex].text; toggleLocField();">
                            <option value="">Select Collection</option>
                            @foreach($collectionTypes ?? [] as $ct)
                                <option value="{{ $ct->id }}"
                                    data-name="{{ $ct->name }}"
                                    data-has-loc="{{ $ct->has_loc_classification ? 'true' : 'false' }}"
                                    data-has-research="{{ $ct->has_research_type ? 'true' : 'false' }}"
                                    {{ (old('collection_type_id', $book->collection_type_id) == $ct->id || old('collection', $book->collection) == $ct->name) ? 'selected' : '' }}>
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
                            <option value="capstone" {{ old('research_type', $book->research_type) == 'capstone' ? 'selected' : '' }}>Capstone</option>
                            <option value="thesis"   {{ old('research_type', $book->research_type) == 'thesis'   ? 'selected' : '' }}>Thesis</option>
                        </select>
                        <small class="field-hint">Classify the type of research work</small>
                    </div>

                    <!-- 8b. Program/Course -->
                    <div class="form-group" id="thesisCourseField" style="display:none;">
                        <label>Program / Course</label>
                        <select name="course_id" id="thesisCourseSelect">
                            <option value="">Select Program</option>
                            @foreach($courses ?? [] as $course)
                                <option value="{{ $course->id }}" {{ old('course_id', $book->course_id) == $course->id ? 'selected' : '' }}>
                                    {{ $course->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="field-hint">Select the program this book belongs to</small>
                    </div>

                    <!-- 9. A-Z Classification -->
                    <div class="form-group loc-field" id="locField">
                        <label>A-Z Classification</label>
                        <input type="text" name="loc_number" id="locNumber" value="{{ old('loc_number', $book->loc_number) }}" placeholder="Select or enter classification">
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
                        <input type="text" name="shelf_location" value="{{ old('shelf_location', $book->shelf_location) }}" placeholder="e.g. Aisle 4, Shelf B-12">
                        <small class="field-hint">Physical location of the book</small>
                    </div>

                    <!-- 12. QR Code / Barcode -->
                    <div class="form-group">
                        <label>QR Code / Barcode</label>
                        <input type="text" name="barcode" id="barcodeInput" value="{{ old('barcode', $book->barcode) }}" placeholder="QR Code number">
                        <small class="field-hint">Unique QR code for this book</small>
                    </div>

                    <!-- 13. Subject/Keywords -->
                    <div class="form-group form-group-full">
                        <label>Subject/Keywords</label>
                        <textarea name="subject" id="subjectInput" rows="3" placeholder="e.g., Mathematics, Programming, Databases">{{ old('subject', $book->subject) }}</textarea>
                        <small class="field-hint">Enter main subject (e.g., Mathematics)</small>
                    </div>

                    <!-- 14. Sub-Keywords -->
                    <div class="form-group form-group-full">
                        <label>Sub-Keywords (Optional)</label>
                        <textarea name="keywords" id="keywordsInput" rows="3" placeholder="e.g., Logic, Financial Math, Algebra, Calculus">{{ old('keywords', $book->keywords) }}</textarea>
                        <small class="field-hint">Separate keywords with commas</small>
                    </div>

                    <!-- 15. Table of Contents Images -->
                    <div class="form-group form-group-full">
                        <label>Table of Contents Images</label>
                        
                        {{-- Display Existing TOC Images --}}
                        @if($book->tocImages && $book->tocImages->count() > 0)
                            <div style="margin-bottom:12px;">
                                <div style="font-size:0.8rem; font-weight:700; color:#374151; margin-bottom:8px;">Existing Pages:</div>
                                <div style="display:flex; flex-wrap:wrap; gap:12px;">
                                    @foreach($book->tocImages as $idx => $tocImg)
                                        <div style="position:relative; width:120px; border:1px solid #e5e7eb; border-radius:8px; overflow:hidden; background:#fff; box-shadow:0 2px 4px rgba(0,0,0,0.05); text-align:center;">
                                            <img src="{{ asset('storage/' . $tocImg->path) }}" alt="TOC Page" style="width:100%; height:110px; object-fit:cover;">
                                            <div style="padding:4px; font-size:0.72rem; color:#555;">Page {{ $idx + 1 }}</div>
                                            <label style="display:block; padding:4px; background:#fef2f2; border-top:1px solid #fecaca; font-size:0.68rem; color:#dc2626; cursor:pointer; font-weight:700;">
                                                <input type="checkbox" name="removed_toc_images[]" value="{{ $tocImg->id }}" style="vertical-align:middle; width:13px; height:13px; accent-color:#dc2626;"> Delete
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="toc-upload-container">
                            <div style="font-size:0.8rem; font-weight:600; color:#4b5563; margin-bottom:6px;">Upload Additional / Replacement TOC Pages:</div>
                            <input type="file" name="toc_images[]" id="tocImagesInput" multiple accept="image/*" onchange="handleTocFiles(this)">
                            <div id="tocPreviewsGrid" class="toc-previews-grid" style="display:flex; flex-wrap:wrap; gap:12px; margin-top:12px;"></div>
                        </div>
                        <small class="field-hint">Pick multiple images to append to Table of Contents</small>
                    </div>

                    <!-- 16. New Acquisition / Donation -->
                    <div class="form-group">
                        <label>Acquisition Flags</label>
                        <div class="checkbox-group">
                            <input type="checkbox" name="is_new_acquisition" value="1" id="newAcq" {{ old('is_new_acquisition', $book->is_new_acquisition) ? 'checked' : '' }}>
                            <label for="newAcq">New Acquisition — recently added to collection</label>
                        </div>
                    </div>

                    <!-- 17. Filipiniana Criteria -->
                    <div class="form-group" id="filipinianaFields" style="display:none;">
                        <label>Filipiniana Criteria</label>
                        <small class="field-hint" style="display:block; margin-bottom:8px; color:#666;">Select the criteria that qualifies this book as Filipiniana:</small>
                        <div class="checkbox-group">
                            <input type="checkbox" name="is_filipino_author" value="1" id="isFilAuthor" {{ old('is_filipino_author', $book->is_filipino_author) ? 'checked' : '' }}>
                            <label for="isFilAuthor">Written by a Filipino author</label>
                        </div>
                        <div class="checkbox-group" style="margin-top:6px;">
                            <input type="checkbox" name="is_ph_published" value="1" id="isPhPub" {{ old('is_ph_published', $book->is_ph_published) ? 'checked' : '' }}>
                            <label for="isPhPub">Published in the Philippines</label>
                        </div>
                        <div class="checkbox-group" style="margin-top:6px;">
                            <input type="checkbox" name="is_ph_subject" value="1" id="isPhSubj" {{ old('is_ph_subject', $book->is_ph_subject) ? 'checked' : '' }}>
                            <label for="isPhSubj">About the Philippines (subject/content)</label>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        Save Changes
                    </button>
                    <a href="{{ route('admin.books') }}" class="btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* PUP Theme Variables */
.edit-book-page {
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

.edit-book-page h2 {
    margin: 0 0 8px 0;
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--pup-maroon);
}

.edit-book-page .page-subtitle {
    color: var(--text-muted);
    font-size: 0.9375rem;
    margin-bottom: 1.75rem;
}

.alert-error {
    background: #FEF2F2;
    color: #991B1B;
    padding: 1rem 1.25rem;
    border-radius: 10px;
    margin-bottom: 1.5rem;
    border-left: 4px solid #EF4444;
}

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
    background: white;
    border-bottom: 1px solid var(--border);
}

.card-header h4 {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text);
}

.card-badge {
    background: rgba(128, 0, 0, 0.1);
    color: var(--pup-maroon);
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.card-body {
    padding: 1.75rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.25rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group-full {
    grid-column: span 2;
}

.form-group label {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text);
    margin-bottom: 0.5rem;
}

.form-group label .required {
    color: #EF4444;
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: 0.625rem 0.875rem;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: 0.9375rem;
    transition: all 0.2s;
    background: white;
    font-family: inherit;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--pup-maroon);
    box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.1);
}

.field-hint {
    color: var(--text-muted);
    font-size: 0.75rem;
    margin-top: 0.375rem;
}

.loc-field {
    grid-column: span 2;
}

.loc-search {
    margin-top: 0.5rem;
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--border);
    border-radius: 6px;
    font-size: 0.875rem;
    width: 100%;
}

.loc-options {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.5rem;
    margin-top: 0.5rem;
    max-height: 200px;
    overflow-y: auto;
    padding: 0.5rem;
    background: #f9fafb;
    border-radius: 8px;
    border: 1px solid var(--border);
}

.loc-option {
    padding: 0.5rem 0.75rem;
    background: white;
    border: 1px solid var(--border);
    border-radius: 6px;
    font-size: 0.8125rem;
    cursor: pointer;
    transition: all 0.2s;
}

.loc-option:hover {
    background: #FDF2F2;
    border-color: var(--pup-maroon);
}

.loc-option.selected {
    background: var(--pup-maroon);
    color: white;
    border-color: var(--pup-maroon);
}

.checkbox-group {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.checkbox-group input[type="checkbox"] {
    width: 1.125rem;
    height: 1.125rem;
    accent-color: var(--pup-maroon);
    cursor: pointer;
}

.checkbox-group label {
    margin-bottom: 0;
    cursor: pointer;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border);
}

.btn-primary {
    background: var(--pup-maroon);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9375rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
}

.btn-primary:hover {
    background: var(--pup-maroon-dark);
    transform: translateY(-1px);
}

.btn-outline {
    background: transparent;
    color: var(--text);
    border: 1px solid var(--border);
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9375rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    transition: all 0.2s;
}

.btn-outline:hover {
    background: #f9fafb;
    border-color: var(--text-muted);
}
</style>

<script>
    const collectionSelect = document.getElementById('collection');
    const locField = document.getElementById('locField');
    const locNumber = document.getElementById('locNumber');
    const locOptionsContainer = document.getElementById('locOptions');
    const locSearch = document.getElementById('locSearch');
    let allOptions = [];

    function toggleLocField() {
        const selectedOpt = collectionSelect.options[collectionSelect.selectedIndex];
        if (!selectedOpt) return;
        const colName = selectedOpt.dataset.name || selectedOpt.text;
        const hasLoc = selectedOpt.dataset.hasLoc === 'true' || ['Library of Congress', 'Filipiniana', 'Circulation'].includes(colName);
        const hasResearch = selectedOpt.dataset.hasResearch === 'true' || colName === 'Research and Innovation' || colName === 'Thesis Collection';
        const isFil = colName === 'Filipiniana';

        if (locField) locField.style.display = hasLoc ? '' : 'none';
        
        const filFields = document.getElementById('filipinianaFields');
        if (filFields) filFields.style.display = isFil ? '' : 'none';

        const thesisField = document.getElementById('thesisCourseField');
        const researchTypeField = document.getElementById('researchTypeField');
        const isbnSection = document.getElementById('isbnSection');

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
                        New Page ${index + 1}
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