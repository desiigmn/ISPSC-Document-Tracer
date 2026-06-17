@php
    $currUser = Auth::user();
    
    // SEQUENCE LOGIC FOR LEFT-BORDER CATEGORIZATION
    $isSharedType = $doc->logs->where('office_id', $currUser->office_id)->where('action', 'DISSEMINATED')->count() > 0;
    if($isSharedType) { $lHex = '#0056B3'; $lMsg = 'SHARED'; }
    elseif($doc->status == 'accepted') { $lHex = '#198754'; $lMsg = 'FINISHED'; }
    elseif($doc->priority == 3) { $lHex = '#dc3545'; $lMsg = 'EX. URGENT'; }
    elseif($doc->priority == 2) { $lHex = '#fd7e14'; $lMsg = 'URGENT'; }
    elseif($doc->priority == 1) { $lHex = '#FFD700'; $lMsg = 'NORMAL'; }
    else { $lHex = '#6C757D'; $lMsg = 'PREPARING'; }
@endphp

{{-- COL 1: TRANSACTION CODE --}}
<td class="ps-5">
    <div class="font-monospace fw-black text-dark" style="font-size: 1.15rem; letter-spacing: -0.5px;">{{ $doc->tracking_id }}</div>
    <small class="text-muted fw-bold ls-1 text-uppercase">{{ $doc->created_at->format('M d, Y') }}</small>
</td>

{{-- COL 2: DESCRIPTION, CREATOR & ALERT --}}
<td>
    <div class="fw-black text-maroon text-uppercase ls-n1 mb-1">{{ $doc->title }}</div>
    <div class="d-flex align-items-center mb-1">
        <i class="fa fa-user-pen text-muted me-2 small"></i>
        <span class="small fw-bold opacity-75">{{ $doc->uploader->username }} / <small class="text-muted">{{ $doc->uploader->office->office_name ?? 'STAFF' }}</small></span>
    </div>
    
    {{-- URGENCY PILL (Academic Palette) --}}
    <span class="badge text-white px-3 py-1 shadow-sm mt-1" style="background: {{ $lHex }}; font-size: 0.55rem; font-weight: 900;">
        <i class="fa fa-tag me-1 opacity-50"></i> {{ $lMsg }}
    </span>
</td>

{{-- COL 3: CURRENT PERSONNEL STATION --}}
<td>
    @php $station = $doc->signatories->where('sign_order', $doc->current_step)->first(); @endphp
    <div class="small text-muted fw-bold mb-1 opacity-50 uppercase" style="font-size: 0.6rem; letter-spacing: 0.5px;">CURRENT STATION:</div>
    <div class="small fw-bold text-dark">
        @if($doc->status == 'accepted')
            <span class="text-success"><i class="fa fa-check-circle"></i> RECORD FINALIZED</span>
        @elseif($doc->status == 'needs_review')
            <span class="text-primary"><i class="fa fa-user-shield"></i> RECORDS AUDIT</span>
        @else
            <i class="fa fa-user-circle text-muted"></i> {{ $station->user->username ?? 'Pending...' }}
        @endif
    </div>
</td>

{{-- COL 4: THE NEW FEATURE (FINAL TARGET DESTINATION) --}}
<td>
    <div class="small text-muted fw-bold mb-1 opacity-50 uppercase" style="font-size: 0.6rem; letter-spacing: 0.5px;">ROUTED:</div>
    <div class="d-flex align-items-center">
        <div class="bg-maroon bg-opacity-10 text-maroon rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 25px; height: 25px;">
            <i class="fa fa-location-dot" style="font-size: 0.75rem;"></i>
        </div>
        <span class="small fw-black text-muted text-truncate" style="max-width: 160px;">
            {{ $doc->targetOffice->office_name ?? 'ADMINISTRATION' }}
        </span>
    </div>
</td>

{{-- COL 5: ACCESS ACTION --}}
<td class="text-center px-4">
    @if($doc->status == 'mapping')
        <a href="{{ route('documents.map', $doc->id) }}" class="btn btn-outline-dark rounded-pill fw-black px-4 py-2 border-2 small shadow-sm hover-elevate">RESUME</a>
    @else
        <a href="{{ route('documents.view', $doc->tracking_id) }}" class="btn btn-maroon rounded-pill fw-black px-4 py-2 small shadow-sm text-white hover-elevate border-2 border-maroon">OPEN<i class="fa fa-eye ms-1"></i></a>
    @endif
</td>