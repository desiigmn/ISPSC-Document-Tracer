<div class="table-responsive bg-white">
    <table class="table align-middle table-hover mb-0 border-0">
        <thead class="bg-light">
            <tr class="small text-muted border-bottom fw-black">
                <th class="ps-5 py-3">TRACKING ID</th>
                <th>DESCRIPTION / URGENCY</th>
                <th>CREATOR</th> <!-- NEW COLUMN -->
                <th>PROCESS STATUS</th>
                <th>LOCATION</th>
                <th class="text-center">ACTION</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tableDocs as $doc)
                @php
                    $isSharedToMe = $doc->logs->where('office_id', Auth::user()->office_id)->where('action', 'DISSEMINATED')->count() > 0;
                    
                    if ($isSharedToMe) { 
                        $statusClass = 'table-row-shared';
                        $label = "OFFICIAL COPY (SHARED)";
                    } elseif ($doc->status == 'accepted') {
                        $statusClass = 'table-row-finished';
                        $label = "FINISHED TRANSACTION";
                    } elseif ($doc->priority == 3) {
                        $statusClass = 'table-row-extremely-urgent';
                        $label = "EXTREMELY URGENT";
                    } elseif ($doc->priority == 2) {
                        $statusClass = 'table-row-urgent';
                        $label = "URGENT";
                    } elseif ($doc->priority == 1) {
                        $statusClass = 'table-row-normal';
                        $label = "NORMAL PRIORITY";
                    } else {
                        $statusClass = 'table-row-mapping';
                        $label = "PREPARING TAGS";
                    }
                @endphp
                <tr class="{{ $statusClass }} shadow-sm mb-1" style="height: 90px;">
                    <td class="ps-5">
                        <small class="d-block text-muted mb-1">{{ $doc->created_at->format('M d, Y') }}</small>
                        <span class="font-monospace fw-black text-dark fs-6">{{ $doc->tracking_id }}</span>
                    </td>
                    
                    <td>
                        <div class="fw-black text-maroon text-uppercase ls-n1">{{ $doc->title }}</div>
                        <span class="badge py-2 px-3 rounded-pill mt-1" 
                              style="background: #f8f9fa; color: #444; border: 1px solid #ddd; font-size: 0.65rem;">
                              <i class="fa fa-shield-halved me-1 text-maroon"></i> {{ $label }}
                        </span>
                    </td>

                    {{-- NEW DATA: THE CREATOR SECTION --}}
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="bg-maroon bg-opacity-10 text-maroon rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                <i class="fa fa-user-pen" style="font-size: 0.8rem;"></i>
                            </div>
                            <div class="lh-1">
                                <div class="fw-bold text-dark small">{{ $doc->uploader->username }}</div>
                                <small class="text-muted" style="font-size: 0.65rem;">{{ strtoupper($doc->uploader->office->office_name ?? 'STAFF') }}</small>
                            </div>
                        </div>
                    </td>

                    <td>
                        <div class="small text-muted mb-1 fw-bold">CURRENTLY AT</div>
                        <div class="small fw-bold">
                            @php 
                                $curr = $doc->signatories->where('sign_order', $doc->current_step)->first(); 
                            @endphp
                            @if($doc->status == 'accepted')
                                <i class="fa fa-check-double text-success"></i> COMPLETED
                            @elseif($doc->status == 'mapping')
                                <i class="fa fa-edit text-primary"></i> Tag Placement
                            @elseif($doc->status == 'needs_review')
                                <i class="fa fa-magnifying-glass text-warning"></i> Records Review
                            @else
                                <div class="d-flex align-items-center">
                                    <i class="fa fa-user-circle text-muted me-1"></i> 
                                    <span class="text-truncate" style="max-width: 150px;">{{ $curr->user->username ?? 'Next Signatory' }}</span>
                                </div>
                            @endif
                        </div>
                    </td>

                    <td>
                        <div class="d-flex align-items-center text-muted">
                            <i class="fa fa-location-arrow me-2 opacity-50"></i>
                            <span class="small fw-bold text-truncate" style="max-width: 180px;">
                                {{ $doc->targetOffice->office_name ?? 'Archive' }}
                            </span>
                        </div>
                    </td>

                    <td class="text-center pe-4">
                        @if($doc->status == 'mapping')
                            <a href="{{ route('documents.map', $doc->id) }}" class="btn btn-outline-info rounded-pill fw-black px-4 shadow-sm border-2">RESUME</a>
                        @else
                            <a href="{{ route('documents.view', $doc->tracking_id) }}" class="btn btn-outline-dark rounded-pill fw-black px-4 shadow-sm border-2">VIEW</a>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>