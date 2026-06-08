<div class="table-responsive">
    <table class="table align-middle table-hover mb-0">
        <thead class="bg-light">
            <tr class="text-muted text-uppercase small fw-bold">
                <th class="ps-4">Tracking ID</th>
                <th>Description</th>
                <th>Personnel</th>
                <th>Location</th>
                <th>Status</th>
                <th class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tableDocs as $doc)
            <tr>
                <!-- 1. Tracking ID -->
                <td class="ps-4">
                    <span class="text-maroon fw-bold font-monospace small">
                        {{ $doc->tracking_id }}
                    </span>
                </td>

                <!-- 2. Description -->
                <td>
                    <div class="fw-bold text-dark">
                        {{ explode(' - ', $doc->title)[0] }}
                    </div>
                    @if($doc->priority == 3 && $doc->status == 'pending')
                        <small class="text-danger fw-bold animate__animated animate__flash animate__infinite">
                            <i class="fa fa-bolt me-1"></i> EXTREMELY URGENT
                        </small>
                    @endif
                </td>

                <!-- 3. Personnel -->
                <td>
                    <div class="fw-bold small text-dark text-uppercase mb-1">
                        {{ $doc->uploader->username }}
                    </div>
                </td>

                <!-- 4. Location -->
                <td>
                    <div class="d-flex align-items-center">
                        <i class="fa fa-building text-muted me-2 small"></i>
                        <span class="small fw-bold text-muted text-uppercase">
                            {{ $doc->targetOffice->office_name ?? 'N/A' }}
                        </span>
                    </div>
                </td>

                <!-- 5. Status -->
                <td>
                    @php $isPending = ($doc->status == 'pending'); @endphp
                    <span class="badge w-100 py-2 fs-6 text-white" style="background-color: {{ $isPending ? '#800000' : '#198754' }};">
                        {{ $isPending ? 'IN TRANSIT' : 'FINISHED' }}
                    </span>
                </td>

                <!-- 6. Action -->
                <td class="text-center">
                    <a href="{{ route('documents.view', $doc->tracking_id) }}" 
                       class="btn btn-sm {{ $style == 'maroon' ? 'btn-outline-danger' : 'btn-outline-dark' }} px-4 rounded-pill fw-bold shadow-sm">
                        TRACK
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>