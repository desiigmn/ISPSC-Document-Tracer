@extends('layouts.ispsc')

@section('title', 'Dashboard | ISPSC ONWARDS UIP')

@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<style>
    :root {
        --ispsc-maroon: #800000;
        --ispsc-yellow: #FFCC00;
        --ispsc-gold: #d4a017;
    }
    
    /* FORCE FULL WIDTH */
    .main-content-fluid {
        width: 100%;
        max-width: 100%;
        padding: 0 40px; /* Extra breathing room on edges */
    }

    .stat-card {
        min-height: 160px; /* Made cards taller */
        border: none;
        border-radius: 15px;
        transition: transform 0.2s;
    }
    .stat-card:hover { transform: translateY(-5px); }

    .text-maroon { color: var(--ispsc-maroon); }
    .btn-maroon { background-color: var(--ispsc-maroon); color: white; border-radius: 8px; }
    .btn-maroon:hover { background-color: #600000; color: white; }

    /* Fix table row alignment */
    .table thead th { 
        border-top: none; 
        font-size: 0.8rem; 
        letter-spacing: 1px; 
        padding: 15px;
    }
    .table tbody td { padding: 15px; }

    nav svg { max-height: 20px; }
</style>
@endpush

@section('content')

<div class="main-content-fluid">
    <!-- HEADER SECTION -->
    <div class="d-flex justify-content-between align-items-end mb-4 pt-2">
        <div>
            <h1 class="fw-bold mb-1" style="color: #800000; font-size: 2.5rem;">System Dashboard</h1>
            <p class="text-muted mb-0 fs-5">
                Welcome back, <strong>{{ Auth::user()->username }}</strong> | <span class="text-maroon fw-bold">{{ Auth::user()->office->office_name ?? 'Global Staff' }}</span>
            </p>
        </div>
        
        <div class="d-flex gap-2 pb-1">
            <a href="{{ route('documents.create') }}" class="btn btn-maroon shadow-sm fw-bold px-4 py-2">
                <i class="fa fa-plus-circle me-2"></i> NEW DOCUMENT
            </a>
        </div>
    </div>

    <!-- SUCCESS ALERT -->
    @if(session('msg'))
        <div class="alert alert-success border-0 shadow-sm mb-4 alert-dismissible fade show p-3" role="alert">
            <i class="fa fa-check-circle me-2"></i> {{ session('msg') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- STATISTICS CARDS (LARGER & FLUID) -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <a href="{{ route('dashboard') }}" class="text-decoration-none">
                <div class="card stat-card shadow-sm text-white" style="background-color: #d4a017;">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <h5 class="text-uppercase fw-bold opacity-75">Total Records</h5>
                        <div class="d-flex justify-content-between align-items-center">
                            <h1 class="display-3 fw-bold mb-0">{{ $countTotal }}</h1>
                            <i class="fa fa-folder-open opacity-25" style="font-size: 4rem;"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="{{ route('dashboard', ['filter' => 'pending']) }}" class="text-decoration-none">
                <div class="card stat-card shadow-sm text-white" style="background-color: #800000;">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <h5 class="text-uppercase fw-bold opacity-75">In Transit</h5>
                        <div class="d-flex justify-content-between align-items-center">
                            <h1 class="display-3 fw-bold mb-0">{{ $countPending }}</h1>
                            <i class="fa fa-truck-moving opacity-25" style="font-size: 4rem;"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="{{ route('dashboard', ['filter' => 'accepted']) }}" class="text-decoration-none">
                <div class="card stat-card shadow-sm text-white" style="background-color: #1b5e20;">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <h5 class="text-uppercase fw-bold opacity-75">Finished</h5>
                        <div class="d-flex justify-content-between align-items-center">
                            <h1 class="display-3 fw-bold mb-0">{{ $countFinished }}</h1>
                            <i class="fa fa-check-double opacity-25" style="font-size: 4rem;"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- TRANSACTION TABLES -->
    @php
        $priorities = [
            ['level' => 3, 'title' => 'Extremely Urgent', 'bg' => 'bg-danger', 'icon' => 'fa-bolt'],
            ['level' => 2, 'title' => 'Urgent', 'bg' => 'bg-warning text-dark', 'icon' => 'fa-exclamation-triangle'],
            ['level' => 1, 'title' => 'Normal / Regular', 'bg' => 'bg-secondary', 'icon' => 'fa-list-ul']
        ];
    @endphp

    @foreach($priorities as $prio)
        @php $filteredDocs = $documents->where('priority', $prio['level']); @endphp

        @if($filteredDocs->count() > 0)
            <div class="card shadow-sm border-0 mb-5" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header {{ $prio['bg'] }} py-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-uppercase tracking-wider">
                        <i class="fa {{ $prio['icon'] }} me-2"></i> {{ $prio['title'] }}
                    </h5>
                    <span class="badge bg-white text-dark rounded-pill px-3">{{ $filteredDocs->count() }} Records</span>
                </div>

                <div class="card-body p-0">
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
                                @foreach($filteredDocs as $doc)
                                <tr>
                                    <td class="ps-4"><span class="text-maroon fw-bold font-monospace">{{ $doc->tracking_id }}</span></td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ explode(' - ', $doc->title)[0] }}</div>
                                        @if($doc->priority == 3)
                                            <small class="text-danger fw-bold animate__animated animate__flash animate__infinite">ACTION REQUIRED ASAP</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold small text-dark">{{ $doc->uploader->username }}</div>
                                    </td>
                                    <td>
                                        <i class="fa fa-building text-muted me-2 small"></i>
                                        <span class="small fw-bold text-muted text-uppercase">{{ $doc->targetOffice->office_name ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $statBg = $doc->status == 'pending' ? '#800000' : ($doc->status == 'accepted' ? '#198754' : '#dc3545');
                                            $statText = $doc->status == 'pending' ? 'IN TRANSIT' : ($doc->status == 'accepted' ? 'FINISHED' : strtoupper($doc->status));
                                        @endphp
                                        <span class="badge w-100 py-2" style="background-color: {{ $statBg }};">{{ $statText }}</span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('documents.view', $doc->tracking_id) }}" class="btn btn-sm btn-outline-danger px-4 rounded-pill fw-bold">
                                            <i class="fa fa-eye me-1"></i> TRACK
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    <!-- PAGINATION -->
    @if($documents->hasPages())
        <div class="d-flex justify-content-center pb-5">
            {{ $documents->links() }}
        </div>
    @endif
</div>
@endsection