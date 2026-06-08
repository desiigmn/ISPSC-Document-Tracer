@extends('layouts.ispsc')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <!-- Add Office Form -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-maroon text-white fw-bold">ADD NEW OFFICE</div>
                <div class="card-body">
                    <form action="{{ route('offices.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="small fw-bold">OFFICE NAME</label>
                            <input type="text" name="office_name" class="form-control" placeholder="e.g. Registrar Office" required>
                        </div>
                        <button class="btn btn-maroon w-100">SAVE OFFICE</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Office List -->
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>OFFICE ID</th>
                                <th>NAME</th>
                                <th>ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($offices as $office)
                            <tr>
                                <td><code>{{ $office->id }}</code></td>
                                <td>{{ $office->office_name }}</td>
                                <td>
                                    @if($office->id !== 'ISPSC-MC-REC-2026-4URQGK') {{-- Protect Records Office --}}
                                    <form action="{{ route('offices.destroy', $office->id) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove office?')">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection