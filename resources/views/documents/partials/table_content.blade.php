<div class="table-responsive">
    <table class="table align-middle table-hover mb-0">
        <thead class="bg-light">
            <tr class="text-muted text-uppercase small fw-bold">
                <th class="ps-4">Tracking ID</th>
                <th>Description</th>
                @if($showRecipients)
                    <th>Recipient Offices</th>
                @else
                    <th>Personnel</th>
                @endif
                <th>Location</th>
                <th class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tableDocs as $doc)
            <tr>
                <td class="ps-4"><span class="text-maroon fw-bold font-monospace small">{{ $doc->tracking_id }}</span></td>
                <td>
                    <div class="fw-bold text-dark">{{ explode(' - ', $doc->title)[0] }}</div>
                    @if($doc->priority == 3 && $doc->status == 'pending')
                        <small class="text-danger fw-bold animate__animated animate__flash animate__infinite">URGENT</small>
                    @endif
                </td>
                
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
                        <div class="fw-bold small text-dark text-uppercase mb-1">{{ $doc->uploader->username }}</div>
                    </td>
                @endif

                <td>
                    <i class="fa fa-building text-muted me-1 small"></i>
                    <span class="small fw-bold text-muted text-uppercase">{{ $doc->targetOffice->office_name ?? 'N/A' }}</span>
                </td>
                <td class="text-center">
                    <a href="{{ route('documents.view', $doc->tracking_id) }}" 
                       class="btn btn-sm {{ $style == 'maroon' ? 'btn-outline-danger' : 'btn-outline-dark' }} px-4 rounded-pill fw-bold shadow-sm">
                        VIEW DOCUMENT
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>