@extends('layouts.student')

@section('title', 'AI Recommendations')

@section('content')
<style>
    .ai-header {
        background: linear-gradient(135deg, #800000 0%, #a00000 100%);
        color: white;
        padding: 25px;
        border-radius: 12px;
        margin-bottom: 24px;
    }
    
    .recommendation-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: all 0.2s;
        border: 1px solid #eee;
    }
    
    .recommendation-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border-color: #800000;
    }
    
    .reason-box {
        background: #f8f9fa;
        padding: 12px 15px;
        border-radius: 8px;
        margin: 12px 0;
        border-left: 3px solid #800000;
        font-size: 14px;
    }
    
    .borrow-btn {
        background: #800000;
        color: white;
        border: none;
        padding: 8px 20px;
        border-radius: 6px;
        font-size: 14px;
        cursor: pointer;
        transition: background 0.2s;
    }
    
    .borrow-btn:hover {
        background: #660000;
    }
    
    .borrow-btn:disabled {
        background: #ccc;
        cursor: not-allowed;
    }
    
    .ai-badge {
        background: rgba(255,255,255,0.2);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
    }
    
    .loading {
        text-align: center;
        padding: 60px 20px;
    }
    
    .spinner {
        width: 50px;
        height: 50px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #800000;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 20px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .personalized-note {
        background: linear-gradient(135deg, #fff5f5 0%, #fff 100%);
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 20px;
        border: 1px solid #ffdddd;
    }
</style>

<div class="ai-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-2">AI Book Recommendations</h4>
            <p class="mb-0">Books matched to your subjects and reading history</p>
        </div>
        <div class="ai-badge">Personalized Recommendations</div>
    </div>
</div>

<div class="personalized-note">
    <div class="d-flex align-items-center">
        <span style="font-size: 24px; margin-right: 12px;">🎯</span>
        <div>
            <strong>Why these recommendations?</strong><br>
            <small>Our AI analyzes what students in your course are reading and what you've borrowed before to suggest books you'll love.</small>
        </div>
    </div>
</div>

<div id="recommendations-container">
    <div class="loading">
        <div class="spinner"></div>
        <p>AI is finding the perfect books for you...</p>
        <small class="text-muted">This may take a few minutes</small>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    fetchRecommendations();
});

function fetchRecommendations() {
    fetch('{{ route("student.ai.recommendations") }}', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        renderRecommendations(data.recommendations);
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('recommendations-container').innerHTML = `
            <div class="alert alert-danger">
                <strong>⚠️ Unable to load recommendations</strong><br>
                Please try again later.
            </div>
        `;
    });
}

function renderRecommendations(recommendations) {
    if (!recommendations || recommendations.length === 0) {
        document.getElementById('recommendations-container').innerHTML = `
            <div class="alert alert-info">
                <strong>📚 No recommendations available yet</strong><br>
                Start borrowing books to get personalized recommendations!
                <div class="mt-3">
                    <a href="{{ route('student.borrow') }}" class="btn btn-primary">Browse Books</a>
                </div>
            </div>
        `;
        return;
    }
    
    let html = '<div class="row">';
    recommendations.forEach((rec, index) => {
        html += `
            <div class="col-12">
                <div class="recommendation-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="mb-1"> ${rec.title || 'Recommended Book'}</h5>
                            <p class="text-muted small mb-2">${rec.author || 'Recommended for you'}</p>
                        </div>
                        <span class="badge bg-primary rounded-pill">${index + 1}</span>
                    </div>
                    <div class="reason-box">
                        <strong>✨ Why you might like this:</strong>
                        <p class="mb-0 mt-2">${rec.reason || 'Based on your interests and what others in your course are reading.'}</p>
                    </div>
                    ${rec.book_id ? `
                        <button class="borrow-btn" onclick="borrowBook(${rec.book_id}, '${rec.title.replace(/'/g, "\\'")}')">
                            📚 Borrow This Book
                        </button>
                    ` : `
                        <button class="borrow-btn" disabled>Currently Unavailable</button>
                    `}
                </div>
            </div>
        `;
    });
    html += '</div>';
    
    document.getElementById('recommendations-container').innerHTML = html;
}

function borrowBook(bookId, bookTitle) {
    if(confirm(`Borrow "${bookTitle}"?`)) {
        fetch('{{ route("student.borrow.post") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ book_id: bookId })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if(data.message && data.message.includes('successfully')) {
                window.location.href = '{{ route("student.dashboard") }}';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
}
</script>
@endsection