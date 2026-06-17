@extends('layouts.ispsc')

@section('title', 'Signature Placement')

@section('content')
<div class="container-fluid py-4">
    @if($document->is_hard_copy)
        {{-- Logic for Hard Copy: Inform user and redirect --}}
        <div class="alert alert-warning shadow border-0 text-center py-5">
            <i class="fa fa-box fa-4x mb-3 text-maroon opacity-50"></i>
            <h3 class="fw-bold">Physical Item Tracking</h3>
            <p>This document is marked as a Hard Copy. Signature placement is not required.</p>
            <a href="{{ route('dashboard') }}" class="btn btn-maroon px-5">Return to Dashboard</a>
        </div>
    @else
        <div class="row">
            <!-- SIDEBAR -->
            <div class="col-lg-3">
                <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                    <div class="card-header bg-maroon text-white py-3 text-center">
                        <h5 class="mb-0 fw-bold text-uppercase small" style="letter-spacing: 1px;">
                            <i class="fa fa-map-marker-alt me-2"></i> Placement Tool
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info py-2 small mb-3 border-0">
                            1. Select a signatory.<br>
                            2. Scroll to the correct page.<br>
                            3. Click the document to place tag.
                        </div>

<div class="list-group list-group-flush mb-4" id="signatoryList" style="max-height: 300px; overflow-y: auto;">
    @foreach($document->signatories as $sig)
        <button type="button" 
                class="list-group-item list-group-item-action signatory-btn d-flex justify-content-between align-items-center border-bottom" 
                data-user-id="{{ $sig->user_id }}">
            
            {{-- Name and Icon Wrapper --}}
            <span class="small fw-bold">
                <i class="fa fa-user-circle me-2"></i> {{ $sig->user->username }}
            </span>

            {{-- The Checkmark Badge (Hidden by default unless x_pos exists) --}}
            <span class="badge rounded-circle {{ $sig->x_pos ? '' : 'd-none' }}" id="check-{{ $sig->user_id }}">
                <i class="fa fa-check"></i>
            </span>
            
        </button>
    @endforeach
</div>

                        <!-- Inside map.blade.php -->
                        <button id="btn-finalize" class="btn btn-success w-100 mb-2 py-3 fw-bold">
                            DONE & FINALIZE <i class="fa fa-check-circle ms-1"></i>
                        </button>
                        <button type="button" onclick="cancelMapping(event)" class="btn btn-outline-danger w-100 fw-bold py-2 shadow-sm">
                            CANCEL & DISCARD <i class="fa fa-trash-alt ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- PDF VIEWER -->
            <div class="col-lg-9">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="fw-bold text-muted small text-uppercase">Document: {{ $document->title }}</span>
                        <span id="page-info" class="badge bg-dark">Preparing PDF...</span>
                    </div>
                    <!-- Fluid container for rendering -->
                    <div id="pdf-viewer-container" class="card-body p-4 bg-secondary d-flex flex-column align-items-center" style="min-height: 85vh; overflow-y: auto;">
                        <div id="loader-spinner" class="text-center text-white mt-5">
                            <div class="spinner-border mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
                            <h5 class="fw-bold">Rendering Document...</h5>
                            <p class="small opacity-75">This may take a few seconds for larger files.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
    :root { --ispsc-maroon: #800000; }
    .bg-maroon { background-color: var(--ispsc-maroon) !important; }
    .text-maroon { color: var(--ispsc-maroon) !important; }

    .pdf-page-wrapper {
        position: relative !important;
        margin-bottom: 40px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.5);
        background: white;
        display: inline-block;
    }

    .marker-overlay {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        z-index: 10; 
        cursor: crosshair;
    }

    .sig-marker {
        position: absolute !important;
        width: 150px; height: 60px;
        background: rgba(255, 204, 0, 0.95);
        border: 2px dashed #800000;
        border-radius: 4px;
        transform: translate(-50%, -50%);
        z-index: 100;
        display: flex; flex-direction: column; justify-content: center; align-items: center;
        pointer-events: all; cursor: grab; color: #800000;
        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    }

    .btn-delete-tag {
        position: absolute; top: -10px; right: -10px;
        background: #dc3545; color: white; border: none;
        width: 24px; height: 24px; border-radius: 50%;
        font-size: 14px; line-height: 1; z-index: 110;
        cursor: pointer; display: flex; align-items: center; justify-content: center;
    }

    .signatory-btn.active { 
        background-color: #800000 !important; 
        color: #FFCC00 !important; 
        border-color: #800000 !important;
    }
        /* SIGNATORY LIST STYLING */
    #signatoryList .signatory-btn {
        background-color: #fff9e6 !important; /* Light Yellow Base */
        color: #800000 !important; /* Maroon Text */
        border: 1px solid #ffcc00 !important;
        margin-bottom: 5px;
        border-radius: 6px !important;
        transition: all 0.2s ease;
    }

    /* HOVER STATE */
    #signatoryList .signatory-btn:hover {
        background-color: #ffe082 !important;
        border-color: #800000 !important;
    }

    /* ACTIVE STATE (When selected to place a tag) */
    #signatoryList .signatory-btn.active {
        background-color: #FFCC00 !important; /* Solid ISPSC Yellow */
        color: #800000 !important;
        border: 2px solid #800000 !important;
        font-weight: 800 !important;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    /* Fix for the icon inside the button */
    #signatoryList .signatory-btn i {
        color: #800000 !important;
    }

    /* The Checkmark Badge */
    #signatoryList .badge.bg-success {
        background-color: #800000 !important; /* Maroon background for check */
        color: #FFCC00 !important; /* Yellow checkmark */
    }
    #signatoryList .badge i {
    color: #ffffff !important; /* Double-ensure the icon is white */
    display: block;
}

/* Make sure the text doesn't overlap the badge */
.signatory-btn span:first-child {
    flex-grow: 1;
    text-align: left;
}
</style>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
<script>
    // 1. Setup Variables
    const pdfUrl = "{{ route('documents.stream', $document->id) }}";
    const signatoriesData = @json($document->signatories); 
    let selectedUserId = null;

    const pdfjsLib = window['pdfjs-dist/build/pdf'];
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';

    // 2. Load PDF
    const loadingTask = pdfjsLib.getDocument(pdfUrl);
    loadingTask.promise.then(pdf => {
        const spinner = document.getElementById('loader-spinner');
        if(spinner) spinner.remove();
        document.getElementById('page-info').innerText = `Total Pages: ${pdf.numPages}`;
        for (let i = 1; i <= pdf.numPages; i++) { renderPage(pdf, i); }

        // Auto-select first signatory
        const firstBtn = document.querySelector('.signatory-btn');
        if(firstBtn) firstBtn.click();
    });

    function renderPage(pdf, pageNum) {
        pdf.getPage(pageNum).then(page => {
            const scale = 1.5;
            const viewport = page.getViewport({ scale: scale });
            const wrapper = document.createElement('div');
            wrapper.className = 'pdf-page-wrapper';
            wrapper.style.width = viewport.width + 'px';
            wrapper.style.height = viewport.height + 'px';

            const canvas = document.createElement('canvas');
            canvas.height = viewport.height; canvas.width = viewport.width;
            const overlay = document.createElement('div');
            overlay.className = 'marker-overlay';
            overlay.dataset.pageNum = pageNum;

            wrapper.appendChild(canvas);
            wrapper.appendChild(overlay);
            document.getElementById('pdf-viewer-container').appendChild(wrapper);
            page.render({ canvasContext: canvas.getContext('2d'), viewport: viewport });

            // Load Existing Tags
            signatoriesData.forEach(sig => {
                if (sig.x_pos && parseInt(sig.page_num || 1) === pageNum) {
                    overlay.appendChild(createMarkerHtml(sig.user_id, sig.user.username, sig.x_pos, sig.y_pos));
                }
            });

            // Click to place
            overlay.addEventListener('click', function(e) {
                if (e.target.closest('.sig-marker') || !selectedUserId) return;
                document.querySelectorAll(`.sig-marker[data-user-id="${selectedUserId}"]`).forEach(el => el.remove());
                const rect = overlay.getBoundingClientRect();
                const x = ((e.clientX - rect.left) / viewport.width) * 100;
                const y = ((e.clientY - rect.top) / viewport.height) * 100;
                const userName = document.querySelector(`.signatory-btn[data-user-id="${selectedUserId}"] span`).innerText.trim();
                overlay.appendChild(createMarkerHtml(selectedUserId, userName, x, y));
                document.getElementById(`check-${selectedUserId}`).classList.remove('d-none');
                savePosition(selectedUserId, x, y, pageNum);
            });
        });
    }

    function createMarkerHtml(userId, name, x, y) {
        const div = document.createElement('div');
        div.className = 'sig-marker shadow'; // We use .sig-marker for validation below
        div.style.left = x + '%'; div.style.top = y + '%';
        div.dataset.userId = userId;
        div.innerHTML = `<button type="button" class="btn-delete-tag" onclick="deleteMarker(event, ${userId})"><i class="fa fa-times"></i></button>
            <div class="text-center lh-1"><div style="font-size: 0.75rem; font-weight: 800;">SIGN HERE</div><div style="font-size: 0.6rem;">${name}</div></div>`;
        return div;
    }

    // 3. Button Actions
    document.querySelectorAll('.signatory-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.signatory-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            selectedUserId = this.dataset.userId;
        });
    });

    function savePosition(userId, x, y, pageNum) {
        fetch("{{ route('documents.saveTag') }}", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            body: JSON.stringify({ doc_id: "{{ $document->id }}", user_id: userId, x: x, y: y, page_num: pageNum })
        });
    }

    window.deleteMarker = function(e, userId) {
        e.stopPropagation();
        e.target.closest('.sig-marker').remove();
        document.getElementById(`check-${userId}`).classList.add('d-none');
        fetch("{{ route('documents.deleteTag') }}", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            body: JSON.stringify({ doc_id: "{{ $document->id }}", user_id: userId })
        });
    };

    // 4. Finalize & Cancel Logic
    document.getElementById('btn-finalize').addEventListener('click', function() {
        const tagsOnScreen = document.querySelectorAll('.sig-marker').length; 
        
        if (tagsOnScreen === 0) {
            Swal.fire({
                title: 'No Tags Placed!',
                text: 'Please click on the document to place a signature tag before finalizing.',
                icon: 'warning',
                confirmButtonColor: '#800000'
            });
        } else {
            // Trigger the back-end finalize route to change status from 'mapping' to 'pending'
            window.location.href = "{{ url('/document/finalize/' . $document->id) }}";
        }
    });

    window.cancelMapping = function(e) {
        Swal.fire({
            title: 'GO BACK TO EDIT?',
            text: "We will discard your current tag progress and return to the form.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#800000',
            confirmButtonText: 'YES, GO BACK'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ url('/document/discard/' . $document->id) }}";
            }
        });
    };
</script>
@endpush