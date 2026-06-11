<div class="table-responsive">
    <table class="table align-middle table-hover mb-0">
        <thead class="bg-light">
            <tr class="text-muted text-uppercase small fw-bold">
                <th class="ps-4">Tracking ID</th>
                <th>Description</th>
                @if($showRecipients)
                    <th>Recipient Offices</th>
                @else
                    <th>Creator</th>
                @endif
                <th>Final Location</th>
                <th>Status</th> <!-- RE-ADDED HEADER -->
                <th class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tableDocs as $doc)
            <tr>
                <!-- 1. TRACKING ID -->
                <td class="ps-4">
                    <span class="text-maroon fw-bold font-monospace small">{{ $doc->tracking_id }}</span>
                </td>

                <!-- 2. DESCRIPTION -->
                <td>
                    <div class="fw-bold text-dark">{{ explode(' - ', $doc->title)[0] }}</div>
                    @if($doc->priority == 3 && $doc->status != 'accepted')
                        <small class="text-danger fw-bold animate__animated animate__flash animate__infinite">
                            <i class="fa fa-bolt"></i> EXTREMELY URGENT
                        </small>
                    @elseif($doc->priority == 2 && $doc->status != 'accepted')
                        <small class="text-warning fw-bold">URGENT</small>
                    @endif
                </td>
                
                <!-- 3. PERSONNEL/RECIPIENTS -->
                @if($showRecipients)
                    <td>
                        @php
                            $recipients = $doc->logs->where('action', 'DISSEMINATED')->pluck('office_id')->unique();
                        @endphp
                        @foreach($recipients as $officeId)
                            @php $off = \App\Models\Office::find($officeId); @endphp
                            <span class="badge bg-light text-primary border mb-1" style="font-size: 0.65rem;">
                                <i class="fa fa-building me-1"></i> {{ $off->office_name ?? $officeId }}
                            </span>
                        @endforeach
                    </td>
                @else
                <td>
                    <div class="fw-bold small text-dark text-uppercase">
                        {{ $doc->uploader->username }}
                    </div>
                </td>
                @endif

                <!-- 4. FINAL LOCATION -->
                <td>
                    <div class="d-flex align-items-center">
                        <i class="fa fa-building text-muted me-2 small"></i>
                        <span class="small fw-bold text-muted text-uppercase">
                            {{ $doc->targetOffice->office_name ?? 'N/A' }}
                        </span>
                    </div>
                </td>

                <!-- 5. STATUS (RE-ADDED DATA) -->
                <td>
                    @php 
                        $isPending = ($doc->status == 'pending' || $doc->status == 'returned'); 
                        $statusText = ($doc->status == 'accepted') ? 'FINISHED' : (($doc->status == 'returned') ? 'RETURNED' : 'IN TRANSIT');
                        $statusColor = ($doc->status == 'accepted') ? '#198754' : (($doc->status == 'returned') ? '#dc3545' : '#800000');
                    @endphp
                    <span class="badge w-100 py-2 fs-6" style="background-color: {{ $statusColor }};">
                        {{ $statusText }}
                    </span>
                </td>

                <!-- 6. ACTION -->
                    <td class="text-center">
                        @php 
                            // Fallback to Maroon if $color is somehow missing
                            $btnColor = $color ?? '#800000'; 
                        @endphp
                        <a href="{{ route('documents.view', $doc->tracking_id) }}" 
                        class="btn btn-sm px-4 rounded-pill fw-bold shadow-sm"
                        style="border: 2px solid {{ $btnColor }}; color: {{ $btnColor }}; background: transparent;"
                        onmouseover="this.style.backgroundColor='{{ $btnColor }}'; this.style.color='white'"
                        onmouseout="this.style.backgroundColor='transparent'; this.style.color='{{ $btnColor }}'">
                            VIEW
                        </a>
                    </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>