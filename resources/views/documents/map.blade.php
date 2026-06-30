@extends('layouts.ispsc')

@section('title', 'Signature & QR Placement')

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

    /* Signatory Buttons */
    .signatory-btn {
        background-color: #fff !important;
        color: #333 !important;
        border: 1px solid #e1e8ed !important;
        margin-bottom: 8px;
        padding: 12px 15px;
        border-radius: 10px !important;
        font-size: 13px;
        transition: 0.2s;
        text-align: left;
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .signatory-btn.active {
        background-color: var(--ispsc-yellow) !important;
        color: var(--ispsc-maroon) !important;
        border: 2px solid var(--ispsc-maroon) !important;
        font-weight: 800;
    }
    .qr-btn.active {
        background-color: #e7f1ff !important;
        color: #0d6efd !important;
        border: 2px solid #0d6efd !important;
    }

    /* PDF Viewer */
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
    }
    .marker-overlay {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        z-index: 10; cursor: crosshair;
    }

    /* Markers */
    .sig-marker, .qr-marker {
        position: absolute !important;
        transform: translate(-50%, -50%);
        z-index: 100;
        cursor: grab;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        display: flex; flex-direction: column; justify-content: center; align-items: center;
        pointer-events: all;
    }
    .sig-marker {
        width: 140px; height: 55px;
        background: rgba(255, 204, 0, 0.9);
        border: 2px dashed var(--ispsc-maroon);
        border-radius: 6px;
        color: var(--ispsc-maroon);
    }
    .qr-marker {
        width: 70px; height: 70px;
        background: white;
        border: 2px solid #000;
        border-radius: 4px;
    }
    .sig-marker:active, .qr-marker:active { cursor: grabbing; }

    .btn-delete-tag {
        position: absolute; top: -10px; right: -10px;
        background: #dc3545; color: white; border: none;
        width: 22px; height: 22px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center; font-size: 10px;
    }

    .btn-finalize { background: #198754; color: #fff; border-radius: 10px; font-weight: 800; padding: 15px; border:none; text-transform: uppercase; }
</style>

<div class="main-content-fluid py-4">
    <div class="row g-4">
        <!-- TOOLBOX -->
        <div class="col-lg-3">
            <div class="tracer-card sticky-top" style="top: 90px;">
                <div class="tracer-card-header bg-dark">
                    <h6 class="text-white mb-0">Placement Tool</h6>
                </div>
                <div class="card-body p-4">
                    
                    <label class="small fw-bold text-muted text-uppercase mb-2 d-block">Signatories</label>
                    <div id="signatoryList" class="mb-4">
                        @foreach($document->signatories as $sig)
                            @php
                                $displayName = $sig->user ? $sig->user->username : ($sig->office->office_name ?? 'Office');
                                $uniqueId = $sig->id; // Use primary key to avoid null issues
                            @endphp
                            <button type="button" class="signatory-btn" data-type="sig" data-id="{{ $uniqueId }}" data-name="{{ $displayName }}">
                                <span><i class="fa {{ $sig->user ? 'fa-user-circle' : 'fa-building' }} me-2 opacity-50"></i> {{ $displayName }}</span>
                                <span class="badge bg-maroon rounded-circle {{ $sig->x_pos ? '' : 'd-none' }}" id="check-sig-{{ $uniqueId }}">
                                    <i class="fa fa-check" style="font-size: 8px;"></i>
                                </span>
                            </button>
                        @endforeach
                    </div>

                    <label class="small fw-bold text-muted text-uppercase mb-2 d-block">System Items</label>
                    <button type="button" class="signatory-btn qr-btn mb-4" data-type="qr" data-id="system-qr">
                        <span><i class="fa fa-qrcode me-2 opacity-50"></i> Document QR Code</span>
                        <span class="badge bg-primary rounded-circle {{ $document->qr_x ? '' : 'd-none' }}" id="check-qr">
                            <i class="fa fa-check" style="font-size: 8px;"></i>
                        </span>
                    </button>

                    <button id="btn-finalize" class="btn-finalize w-100 shadow-sm mb-2">
                        FINALIZE MAPPING <i class="fa fa-check-circle ms-1"></i>
                    </button>
                    
                    <a href="{{ route('documents.discard', $document->id) }}" class="btn btn-outline-danger w-100 fw-bold small py-2">
                        DISCARD & EXIT
                    </a>
                </div>
            </div>
        </div>

        <!-- PDF AREA -->
        <div class="col-lg-9">
            <div class="tracer-card shadow-lg">
                <div class="tracer-card-header">
                    <h6 class="text-muted"><i class="fa fa-file-pdf me-2 text-maroon"></i> {{ strtoupper($document->title) }}</h6>
                    <span id="page-info" class="badge bg-dark">Loading Document...</span>
                </div>
                
                <div id="pdf-viewer-container">
                    <div id="loader-spinner" class="text-center text-white mt-5">
                        <div class="spinner-border mb-3" role="status"></div>
                        <h5 class="fw-bold">Rendering PDF Pages...</h5>
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
    const pdfUrl = "{{ route('documents.stream', $document->id) }}";
    const signatoriesData = @json($document->signatories->load(['user', 'office'])); 
    const docData = @json($document);

    let selectedType = null;
    let selectedId = null;   // This will hold the Signatory PK ($sig->id)
    let selectedName = null;

    const pdfjsLib = window['pdfjs-dist/build/pdf'];
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';

    // Load PDF
    pdfjsLib.getDocument(pdfUrl).promise.then(pdf => {
        document.getElementById('loader-spinner')?.remove();
        document.getElementById('page-info').innerText = `Total Pages: ${pdf.numPages}`;
        for (let i = 1; i <= pdf.numPages; i++) { renderPage(pdf, i); }
    });

    function renderPage(pdf, pageNum) {
        pdf.getPage(pageNum).then(page => {
            const viewport = page.getViewport({ scale: 1.5 });
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

            // Render existing markers
            signatoriesData.forEach(sig => {
                if (sig.x_pos && parseInt(sig.page_num || 1) === pageNum) {
                    const name = sig.user ? sig.user.username : (sig.office ? sig.office.office_name : 'Signatory');
                    overlay.appendChild(createSigMarker(sig.id, name, sig.x_pos, sig.y_pos));
                }
            });

            if (docData.qr_x && parseInt(docData.qr_page || 1) === pageNum) {
                overlay.appendChild(createQrMarker(docData.qr_x, docData.qr_y));
            }

            // Click to Place
            overlay.addEventListener('click', function(e) {
                if (e.target.closest('.sig-marker') || e.target.closest('.qr-marker') || !selectedType) return;

                const rect = overlay.getBoundingClientRect();
                const x = ((e.clientX - rect.left) / viewport.width) * 100;
                const y = ((e.clientY - rect.top) / viewport.height) * 100;

                if (selectedType === 'sig') {
                    document.querySelectorAll(`.sig-marker[data-id="${selectedId}"]`).forEach(el => el.remove());
                    overlay.appendChild(createSigMarker(selectedId, selectedName, x, y));
                    document.getElementById(`check-sig-${selectedId}`).classList.remove('d-none');
                    savePos('sig', selectedId, x, y, pageNum);
                } else if (selectedType === 'qr') {
                    document.querySelectorAll('.qr-marker').forEach(el => el.remove());
                    overlay.appendChild(createQrMarker(x, y));
                    document.getElementById('check-qr').classList.remove('d-none');
                    savePos('qr', null, x, y, pageNum);
                }
            });
        });
    }

function createSigMarker(id, name, x, y) {
        const div = document.createElement('div');
        // Ensure sig-marker is the FIRST class
        div.className = 'sig-marker shadow'; 
        div.style.left = x + '%'; 
        div.style.top = y + '%';
        div.dataset.id = id;
        
        // This ensures the element is physically "real" in the HTML
        div.setAttribute('class', 'sig-marker shadow'); 
        
        div.innerHTML = `
            <button type="button" class="btn-delete-tag" onclick="deleteTag(event, 'sig', ${id})">
                <i class="fa fa-times"></i>
            </button>
            <div class="text-center lh-1">
                <div style="font-size: 10px; font-weight: 800;">SIGN HERE</div>
                <div style="font-size: 9px;">${name}</div>
            </div>`;
        return div;
    }

    function createQrMarker(x, y) {
        const div = document.createElement('div');
        div.className = 'qr-marker shadow';
        div.style.left = x + '%'; div.style.top = y + '%';
        div.innerHTML = `<button type="button" class="btn-delete-tag" onclick="deleteTag(event, 'qr', null)"><i class="fa fa-times"></i></button>
            <i class="fa fa-qrcode fa-2x"></i><div style="font-size: 8px; font-weight: 800;">QR CODE</div>`;
        return div;
    }

    document.querySelectorAll('.signatory-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.signatory-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            selectedType = this.dataset.type;
            selectedId = this.dataset.id;
            selectedName = this.dataset.name || '';
        });
    });

function savePos(type, id, x, y, page) {
    // If type is 'qr', we hit a different route
    const url = type === 'sig' ? "{{ route('documents.saveTag') }}" : "{{ route('documents.saveQrTag') }}";
    
    fetch(url, {
        method: "POST",
        headers: { 
            "Content-Type": "application/json", 
            "X-CSRF-TOKEN": "{{ csrf_token() }}" 
        },
        body: JSON.stringify({ 
            doc_id: "{{ $document->id }}", 
            signatory_id: id, // null for QR
            x: x, 
            y: y, 
            page_num: page 
        })
    }).then(response => {
        if (response.ok) {
            if (type === 'sig') {
                document.getElementById(`check-sig-${id}`).classList.remove('d-none');
            } else {
                document.getElementById('check-qr').classList.remove('d-none');
            }
        }
    });
}

    // FINALIZING LOGIC
    document.getElementById('btn-finalize').addEventListener('click', function(e) {
        e.preventDefault();
        
        // Count how many checkmarks are visible in the sidebar
        const savedSignatures = document.querySelectorAll('#signatoryList .badge:not(.d-none)').length;
        
        if (savedSignatures === 0) {
            return Swal.fire({
                title: 'No Tags Placed!',
                text: 'You must select a name from the sidebar and click on the document to place their signature tag.',
                icon: 'warning',
                confirmButtonColor: '#800000'
            });
        }

        Swal.fire({
            title: 'Finalize Placement?',
            text: "This will set the signature positions and move the document to the next phase.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            confirmButtonText: 'Yes, Finalize'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ route('documents.finalize', $document->id) }}";
            }
        });
    });
</script>
@endpush