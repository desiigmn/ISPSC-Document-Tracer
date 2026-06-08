@extends('layouts.ispsc')

@section('title', 'Signature Placement')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <!-- SIDEBAR -->
        <div class="col-lg-3">
            <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                <div class="card-header bg-maroon text-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fa fa-map-marker-alt me-2"></i> Placement Tool</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info py-2 small mb-3">
                        1. Select a signatory.<br>
                        2. Scroll to the correct page.<br>
                        3. Click the document to place tag.
                    </div>

                    <div class="list-group list-group-flush mb-4" id="signatoryList">
                        @foreach($document->signatories as $sig)
                            <button type="button" 
                                    class="list-group-item list-group-item-action signatory-btn d-flex justify-content-between align-items-center" 
                                    data-user-id="{{ $sig->user_id }}">
                                <span><i class="fa fa-user-circle me-2"></i> {{ $sig->user->username }}</span>
                                <span class="badge rounded-pill bg-success {{ $sig->x_pos ? '' : 'd-none' }}" id="check-{{ $sig->user_id }}">
                                    <i class="fa fa-check"></i>
                                </span>
                            </button>
                        @endforeach
                    </div>

                    <a href="{{ route('dashboard') }}" class="btn btn-success w-100 fw-bold py-2 shadow">
                        DONE & FINALIZE <i class="fa fa-check-circle ms-1"></i>
                    </a>
                    <button type="button" onclick="cancelMapping()" class="btn btn-outline-danger w-100 fw-bold py-2 shadow mt-2">
                        CANCEL & DISCARD <i class="fa fa-trash-alt ms-1"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- PDF VIEWER -->
        <div class="col-lg-9">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-light d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="fw-bold text-muted small">DOCUMENT: {{ $document->title }}</span>
                    <span id="page-info" class="badge bg-dark">Loading Document...</span>
                </div>
                <!-- This container holds the rendered pages -->
                <div id="pdf-viewer-container" class="card-body p-4 bg-secondary d-flex flex-column align-items-center" style="min-height: 85vh; overflow-y: auto;">
                    <!-- Pages will be injected here via JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    :root { --ispsc-maroon: #800000; }
    .bg-maroon { background-color: var(--ispsc-maroon); }

    .pdf-page-wrapper {
        position: relative !important;
        margin-bottom: 40px;
        box-shadow: 0 0 20px rgba(0,0,0,0.5);
        background: white;
        display: inline-block;
    }

    .marker-overlay {
        position: absolute;
        top: 0; 
        left: 0; 
        width: 100%; 
        height: 100%;
        z-index: 10; 
        cursor: crosshair;
    }

    .sig-marker {
        position: absolute !important;
        width: 145px; 
        height: 65px;
        background: rgba(255, 204, 0, 0.95);
        border: 2px dashed var(--ispsc-maroon);
        border-radius: 4px;
        transform: translate(-50%, -50%);
        z-index: 100;
        text-align: center;
        display: flex; 
        flex-direction: column; 
        justify-content: center;
        pointer-events: all;
        cursor: grab;
        color: var(--ispsc-maroon);
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }

    .btn-delete-tag {
        position: absolute; 
        top: -10px; 
        right: -10px;
        background: #dc3545; 
        color: white; 
        border: none;
        width: 22px; 
        height: 22px; 
        border-radius: 50%;
        font-size: 14px; 
        line-height: 1; 
        z-index: 110;
        cursor: pointer;
    }

    .btn-delete-tag:hover { background: #a71d2a; }

    .signatory-btn.active { 
        background-color: var(--ispsc-maroon) !important; 
        color: white !important; 
    }
</style>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
<script>
    const pdfUrl = "{{ asset('storage/' . $document->file_path) }}";
    let selectedUserId = null;
    // Pass signatories and existing positions from Controller
    const signatoriesData = @json($document->signatories); 

    const pdfjsLib = window['pdfjs-dist/build/pdf'];
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';

    // 1. Initialize PDF loading
    pdfjsLib.getDocument(pdfUrl).promise.then(pdf => {
        document.getElementById('page-info').innerText = `Total Pages: ${pdf.numPages}`;
        
        // Loop through all pages and render them
        for (let i = 1; i <= pdf.numPages; i++) {
            renderPage(pdf, i);
        }

        // Auto-select if only one signatory
        const btns = document.querySelectorAll('.signatory-btn');
        if(btns.length === 1) btns[0].click();
    });

    function renderPage(pdf, pageNum) {
        pdf.getPage(pageNum).then(page => {
            const scale = 1.5;
            const viewport = page.getViewport({ scale: scale });
            
            const wrapper = document.createElement('div');
            wrapper.className = 'pdf-page-wrapper';
            wrapper.dataset.pageNum = pageNum;
            wrapper.style.width = viewport.width + 'px';
            wrapper.style.height = viewport.height + 'px';

            const canvas = document.createElement('canvas');
            canvas.height = viewport.height;
            canvas.width = viewport.width;

            const overlay = document.createElement('div');
            overlay.className = 'marker-overlay';
            overlay.dataset.pageNum = pageNum;

            wrapper.appendChild(canvas);
            wrapper.appendChild(overlay);
            document.getElementById('pdf-viewer-container').appendChild(wrapper);

            page.render({ canvasContext: canvas.getContext('2d'), viewport: viewport });

            // 2. Load existing markers saved in database for THIS specific page
            signatoriesData.forEach(sig => {
                // Assuming your signatories table has a 'page_num' column
                if (sig.x_pos && parseInt(sig.page_num || 1) === pageNum) {
                    overlay.appendChild(createMarkerHtml(sig.user_id, sig.user.username, sig.x_pos, sig.y_pos));
                }
            });

            // 3. Click event listener to place a new tag
            overlay.addEventListener('click', function(e) {
                if (e.target.closest('.sig-marker')) return; // Don't place a tag if clicking a delete button
                
                if (!selectedUserId) {
                    alert("Please select a signatory from the sidebar first!");
                    return;
                }

                // BUG FIX: AGGRESSIVE CLEANUP
                // This removes any existing tag for this specific user from ALL pages in the viewer
                document.querySelectorAll(`.sig-marker[data-user-id="${selectedUserId}"]`).forEach(el => el.remove());

                const rect = overlay.getBoundingClientRect();
                const x = ((e.clientX - rect.left) / viewport.width) * 100;
                const y = ((e.clientY - rect.top) / viewport.height) * 100;

                const userName = document.querySelector(`.signatory-btn[data-user-id="${selectedUserId}"] span`).innerText.trim();
                const newMarker = createMarkerHtml(selectedUserId, userName, x, y);
                
                overlay.appendChild(newMarker);
                
                // Show checkmark on sidebar
                document.getElementById(`check-${selectedUserId}`).classList.remove('d-none');

                // Save to database via AJAX
                savePosition(selectedUserId, x, y, pageNum);
            });
        });
    }

    function createMarkerHtml(userId, name, x, y) {
        const div = document.createElement('div');
        div.className = 'sig-marker shadow';
        div.style.left = x + '%';
        div.style.top = y + '%';
        div.dataset.userId = userId;
        div.innerHTML = `
            <button type="button" class="btn-delete-tag" onclick="deleteMarker(event, ${userId})">&times;</button>
            <div class="marker-content p-1">
                <div class="small fw-bold">SIGN HERE</div>
                <div style="font-size: 0.65rem; line-height: 1;">${name}</div>
            </div>
        `;
        return div;
    }

    // Signatory Selection
    document.querySelectorAll('.signatory-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.signatory-btn').forEach(b => b.classList.remove('active', 'bg-maroon', 'text-white'));
            this.classList.add('active', 'bg-maroon', 'text-white');
            selectedUserId = this.dataset.userId;
        });
    });

    function savePosition(userId, x, y, pageNum) {
        fetch("{{ route('documents.saveTag') }}", {
            method: "POST",
            headers: { 
                "Content-Type": "application/json", 
                "X-CSRF-TOKEN": "{{ csrf_token() }}" 
            },
            body: JSON.stringify({ 
                doc_id: "{{ $document->id }}", 
                user_id: userId, 
                x: x, 
                y: y, 
                page_num: pageNum 
            })
        }).then(res => res.json()).then(data => {
            console.log("Position saved for user " + userId);
        }).catch(err => console.error("Save error:", err));
    }

    window.deleteMarker = function(e, userId) {
        e.stopPropagation();
        const marker = e.target.closest('.sig-marker');
        if (marker) marker.remove();
        
        // Hide checkmark on sidebar
        document.getElementById(`check-${userId}`).classList.add('d-none');
        
        fetch("{{ route('documents.deleteTag') }}", {
            method: "POST",
            headers: { 
                "Content-Type": "application/json", 
                "X-CSRF-TOKEN": "{{ csrf_token() }}" 
            },
            body: JSON.stringify({ 
                doc_id: "{{ $document->id }}", 
                user_id: userId 
            })
        }).catch(err => console.error("Delete error:", err));
    };

    window.cancelMapping = function() {
    if (confirm("Are you sure you want to cancel? This will permanently delete the document and all uploaded files.")) {
        
        // Show loading state on the button
        const btn = event.target.closest('button');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i> Discarding...';

        fetch("{{ route('documents.delete', $document->id) }}", {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'OK') {
                // Redirect back to dashboard after successful deletion
                window.location.href = "{{ route('dashboard') }}";
            } else {
                alert("Error discarding document.");
                btn.disabled = false;
                btn.innerHTML = 'CANCEL & DISCARD <i class="fa fa-trash-alt ms-1"></i>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.location.href = "{{ route('dashboard') }}";
        });
    }
};
</script>
@endpush