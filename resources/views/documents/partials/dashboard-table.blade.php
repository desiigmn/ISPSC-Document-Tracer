<div class="table-responsive">
    <table class="table align-middle table-hover mb-0">
        <thead class="bg-light">
            <tr class="text-muted text-uppercase small fw-bold">
                <th class="ps-4">Tracking ID</th>
                <th>Description</th>
                <th>Creator</th>
                <th>Location</th>
                <th>Status</th>
                <th class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tableDocs as $doc)
            <tr>
                <td class="ps-4"><span class="text-maroon fw-bold font-monospace small">{{ $doc->tracking_id }}</span></td>
                <td><div class="fw-bold text-dark">{{ explode(' - ', $doc->title)[0] }}</div></td>
                <td>
                    <div class="fw-bold small text-dark text-uppercase">{{ $doc->uploader->username }}</div>
                    @if(Auth::id() == $doc->uploader_id)
                        <span class="badge bg-info text-dark" style="font-size: 0.6rem;">CREATOR</span>
                    @else
                        <span class="badge bg-secondary" style="font-size: 0.6rem;">RECEIVER</span>
                    @endif
                </td>
                <td><span class="small fw-bold text-muted text-uppercase">{{ $doc->targetOffice->office_name ?? 'N/A' }}</span></td>
                <td>
                    @php $isPending = ($doc->status == 'pending'); @endphp
                    <span class="badge w-100 py-2 {{ $isPending ? 'bg-maroon' : 'bg-success' }}">
                        {{ $isPending ? 'IN TRANSIT' : 'FINISHED' }}
                    </span>
                </td>
                <td class="text-center">
                    <a href="{{ route('documents.view', $doc->tracking_id) }}" class="btn btn-sm {{ $style == 'maroon' ? 'btn-outline-danger' : 'btn-outline-dark' }} px-4 rounded-pill fw-bold">TRACK</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>