@extends('layouts.ispsc')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar: List of Signatories -->
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-header bg-maroon text-white">Placement Instructions</div>
                <div class="card-body">
                    <p class="small text-muted">Select a signatory below, then click on the document where they should sign.</p>
                    <div id="signatory-list">
                        @foreach($document->signatories as $index => $sig)
                            <button class="btn btn-outline-secondary btn-sm w-100 mb-2 text-start sig-selector" 
                                    data-name="{{ $sig->name }}" id="btn-{{ $index }}">
                                <i class="fa fa-user"></i> {{ $sig->name }}
                            </button>
                        @endforeach
                    </div>
                    <hr>
                    <form action="{{ route('documents.save-positions', $document->id) }}" method="POST">
                        @csrf
                        <div id="coords-input-container"></div>
                        <button type="submit" class="btn btn-success w-100">SAVE & FINALIZE</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- PDF Viewer Area -->
        <div class="col-md-9">
            <div id="pdf-container" style="position: relative; border: 2px solid #800000; overflow: auto; background: #eee;">
                <canvas id="pdf-render"></canvas>
                <!-- Markers will appear here via JS -->
            </div>
        </div>
    </div>
</div>

<style>
    .bg-maroon { background-color: #800000; }
    .sig-marker {
        position: absolute;
        width: 150px;
        height: 50px;
        border: 2px dashed #800000;
        background: rgba(255, 204, 0, 0.3);
        color: #800000;
        font-weight: bold;
        text-align: center;
        line-height: 50px;
        cursor: move;
        font-size: 10px;
        pointer-events: none;
    }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
<script>
    const url = "{{ asset('storage/' . $document->file_path) }}";
    let selectedSignatory = null;

    // Initialize PDF.js
    const pdfjsLib = window['pdfjs-dist/build/pdf'];
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';

    pdfjsLib.getDocument(url).promise.then(pdf => {
        pdf.getPage(1).then(page => {
            const canvas = document.getElementById('pdf-render');
            const context = canvas.getContext('2d');
            const viewport = page.getViewport({ scale: 1.5 });
            canvas.height = viewport.height;
            canvas.width = viewport.width;

            page.render({ canvasContext: context, viewport: viewport });
        });
    });

    // Handle Signatory Selection
    document.querySelectorAll('.sig-selector').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.sig-selector').forEach(b => b.classList.replace('btn-primary', 'btn-outline-secondary'));
            this.classList.replace('btn-outline-secondary', 'btn-primary');
            selectedSignatory = this.getAttribute('data-name');
        });
    });

    // Handle Clicking on PDF to Place Signature
    document.getElementById('pdf-container').addEventListener('click', function(e) {
        if (!selectedSignatory) {
            alert("Please select a signatory from the left first!");
            return;
        }

        const rect = this.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        // Convert to percentage for responsiveness
        const xPercent = (x / rect.width) * 100;
        const yPercent = (y / rect.height) * 100;

        // Create visible marker
        const marker = document.createElement('div');
        marker.className = 'sig-marker';
        marker.style.left = xPercent + '%';
        marker.style.top = yPercent + '%';
        marker.innerText = "SIGN HERE: " + selectedSignatory;
        this.appendChild(marker);

        // Add hidden inputs for the form
        const inputContainer = document.getElementById('coords-input-container');
        inputContainer.insertAdjacentHTML('beforeend', `
            <input type="hidden" name="positions[${selectedSignatory}][x]" value="${xPercent}">
            <input type="hidden" name="positions[${selectedSignatory}][y]" value="${yPercent}">
        `);

        selectedSignatory = null; // Reset selection
    });
</script>
@endsection