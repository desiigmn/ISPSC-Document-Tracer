@extends('layouts.ispsc')

@section('title', 'Signature Placement')

@section('content')
<style>
    :root { 
        --ispsc-maroon: #800000; 
        --ispsc-yellow: #FFCC00;
        --bg-pro-grey: #525659;
    }

    .main-content-fluid { width: 100%; padding: 0 40px; }

    .tracer-card { 
        background: #fff; border: 1px solid #e1e8ed; border-radius: 12px; 
        margin-bottom: 25px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); overflow: hidden; 
    }
    .tracer-card-header { 
        padding: 15px 25px; border-bottom: 1px solid #f1f1f1; 
        display: flex; justify-content: space-between; align-items: center; background: #fff; 
    }
    .tracer-card-header h6 { margin: 0; font-weight: 800; color: #000; text-transform: uppercase; font-size: 13px; }

    #signatoryList .signatory-btn {
        background-color: #fff !important;
        color: #333 !important;
        border: 1px solid #e1e8ed !important;
        margin-bottom: 8px;
        padding: 15px;
        border-radius: 10px !important;
        font-size: 14px;
        transition: 0.2s;
    }
    #signatoryList .signatory-btn.active {
        background-color: var(--ispsc-yellow) !important;
        color: var(--ispsc-maroon) !important;
        border: 2px solid var(--ispsc-maroon) !important;
        font-weight: 800;
    }

    #pdf-viewer-container {
        background-color: var(--bg-pro-grey);
        padding: 40px;
        min-height: 85vh;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .pdf-page-wrapper {
        position: relative !important;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.4);
        background: white;
        user-select: none; /* Prevent text selection while dragging */
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
        border: 2px dashed var(--ispsc-maroon);
        border-radius: 6px;
        transform: translate(-50%, -50%);
        z-index: 100;
        display: flex; flex-direction: column; justify-content: center; align-items: center;
        cursor: grab; color: var(--ispsc-maroon);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        pointer-events: all;
    }
    .sig-marker:active { cursor: grabbing; }

    .btn-delete-tag {
        position: absolute; top: -12px; right: -12px;
        background: #dc3545; color: white; border: none;
        width: 26px; height: 26px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer;
    }

    .btn-finalize { background: #198754; color: #fff; border-radius: 10px; font-weight: 800; padding: 15px; border:none; text-transform: uppercase; font-size: 14px; }
    .btn-discard { background: transparent; color: #dc3545; border: 2px solid #dc3545; border-radius: 10px; font-weight: 800; padding: 10px; text-transform: uppercase; font-size: 12px; margin-top: 10px; }
</style>

<div class="main-content-fluid py-4">
    <div class="row g-4">
        <!-- SIDEBAR TOOLBOX -->
        <div class="col-lg-3">
            <div class="tracer-card sticky-top" style="top: 90px;">
                <div class="tracer-card-header bg-maroon">
                    <h6 class="text-white mb-0">Placement Tool</h6>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-light border small mb-4" style="font-size: 13px;">
                        <i class="fa fa-info-circle text-primary me-2"></i>
                        1. Select a signatory.<br>
                        2. Click PDF to place tag.<br>
                        3. Drag tag to move it.
                    </div>

                    <div class="list-group list-group-flush mb-4" id="signatoryList" style="max-height: 400px; overflow-y: auto;">
                        @foreach($document->signatories as $sig)
                            <button type="button" class="list-group-item list-group-item-action signatory-btn d-flex justify-content-between align-items-center" data-user-id="{{ $sig->user_id }}">
                                <span><i class="fa fa-user-circle me-2 opacity-50"></i> {{ $sig->user->username }}</span>
                                <span class="badge bg-maroon rounded-circle {{ $sig->x_pos ? '' : 'd-none' }}" id="check-{{ $sig->user_id }}">
                                    <i class="fa fa-check p-1" style="font-size: 8px;"></i>
                                </span>
                            </button>
                        @endforeach
                    </div>

                    <button id="btn-finalize" class="btn-finalize w-100 shadow-sm">
                        FINALIZE MAPPING <i class="fa fa-check-circle ms-1"></i>
                    </button>
                    
                    <button type="button" onclick="cancelMapping(event)" class="btn-discard w-100">
                        CANCEL & DISCARD
                    </button>
                </div>
            </div>
        </div>

        <!-- PDF DISPLAY AREA -->
        <div class="col-lg-9">
            <div class="tracer-card shadow-lg">
                <div class="tracer-card-header">
                    <h6 class="text-muted"><i class="fa fa-file-pdf me-2 text-maroon"></i> {{ strtoupper($document->title) }}</h6>
                    <span id="page-info" class="badge bg-dark">Loading...</span>
                </div>
                
                <div id="pdf-viewer-container">
                    <div id="loader-spinner" class="text-center text-white mt-5">
                        <div class="spinner-border mb-3" role="status"></div>
                        <h5 class="fw-bold">Generating View...</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
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