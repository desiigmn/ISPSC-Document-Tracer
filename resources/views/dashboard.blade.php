@extends('layouts.ispsc')

@section('title', 'Dashboard | ISPSC ONWARDS UIP')

@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<style>
    :root { --ispsc-maroon: #800000; --ispsc-yellow: #FFCC00; --ispsc-gold: #d4a017; }
    .main-content-fluid { width: 100%; max-width: 100%; padding: 0 40px; }
    .stat-card { min-height: 160px; border: none; border-radius: 15px; transition: transform 0.2s; }
    .stat-card:hover { transform: translateY(-5px); }
    .text-maroon { color: var(--ispsc-maroon); }
    .btn-maroon { background-color: var(--ispsc-maroon); color: white; border-radius: 8px; }
    .btn-maroon:hover { background-color: #600000; color: white; }
    .table thead th { border-top: none; font-size: 0.85rem; letter-spacing: 1px; padding: 18px; background-color: #f8f9fa; }
    .table tbody td { padding: 18px; font-size: 1rem; }
    .bg-icon-lg { font-size: 4rem; opacity: 0.15; }
    nav svg { max-height: 20px; }
</style>
@endpush

@section('content')
<div class="main-content-fluid">
    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-end mb-4 pt-2">
        <div>
            <h1 class="fw-bold mb-1" style="color: #800000; font-size: 2.5rem;">System Dashboard</h1>
            <p class="text-muted mb-0 fs-5">
                Welcome back, <strong>{{ Auth::user()->username }}</strong> | <span class="text-maroon fw-bold text-uppercase">{{ Auth::user()->office->office_name ?? 'Staff' }}</span>
            </p>
        </div>
        <div class="d-flex gap-2 pb-1">
            @if(Auth::user()->role === 'superadmin')
                <a href="{{ route('admin.personnel') }}" class="btn btn-dark shadow-sm fw-bold px-4 py-3"><i class="fa fa-users-cog me-2"></i> PERSONNEL</a>
            @endif
            <a href="{{ route('documents.create') }}" class="btn btn-maroon shadow-sm fw-bold px-4 py-3"><i class="fa fa-plus-circle me-2"></i> NEW DOCUMENT</a>
        </div>
    </div>

    @if(session('msg'))
        <div class="alert alert-success border-0 shadow-sm mb-4 alert-dismissible fade show p-3" role="alert">
            <i class="fa fa-check-circle me-2"></i> {{ session('msg') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- STATISTICS CARDS -->
    <div class="row g-4 mb-5">
        <div class="col-xl-3 col-md-6">
            <a href="{{ route('dashboard') }}" class="text-decoration-none">
                <div class="card stat-card shadow-sm text-white" style="background-color: #d4a017;">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <h6 class="text-uppercase fw-bold opacity-80">Total Records</h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <h1 class="display-3 fw-bold mb-0">{{ $countTotal }}</h1>
                            <i class="fa fa-folder-open bg-icon-lg"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-xl-3 col-md-6">
            <a href="{{ route('dashboard', ['filter' => 'pending']) }}" class="text-decoration-none">
                <div class="card stat-card shadow-sm text-white" style="background-color: #800000;">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <h6 class="text-uppercase fw-bold opacity-80">In Transit</h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <h1 class="display-3 fw-bold mb-0">{{ $countPending }}</h1>
                            <i class="fa fa-truck-moving bg-icon-lg"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-xl-3 col-md-6">
            <a href="{{ route('dashboard', ['filter' => 'accepted']) }}" class="text-decoration-none">
                <div class="card stat-card shadow-sm text-white" style="background-color: #1b5e20;">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <h6 class="text-uppercase fw-bold opacity-80">Finished</h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <h1 class="display-3 fw-bold mb-0">{{ $countFinished }}</h1>
                            <i class="fa fa-check-double bg-icon-lg"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-xl-3 col-md-6">
            <a href="{{ route('dashboard', ['filter' => 'shared']) }}" class="text-decoration-none">
                <div class="card stat-card shadow-sm text-white" style="background-color: #0056b3;">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <h6 class="text-uppercase fw-bold opacity-80">Shared Copies</h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <h1 class="display-3 fw-bold mb-0">{{ $countShared }}</h1>
                            <i class="fa fa-share-nodes bg-icon-lg"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- DYNAMIC TITLE -->
    <div class="mb-4">
        <h3 class="fw-bold text-dark text-uppercase">
            @if(request('filter') == 'shared') Shared Copies @elseif(request('filter') == 'accepted') Finished Transactions @else All Records @endif
        </h3>
        <hr>
    </div>

@php
    $filter = request('filter');
    $currUser = Auth::user();
    $isAdminOrRecords = ($currUser->role === 'superadmin' || str_contains($currUser->office_id ?? '', '-REC-'));

    // 1. Logic for Priority Tables (Calculated for top 3 tables)
    $priorities = [
        ['level' => 3, 'title' => 'Extremely Urgent', 'bg' => 'bg-danger', 'icon' => 'fa-bolt'],
        ['level' => 2, 'title' => 'Urgent', 'bg' => 'bg-warning text-dark', 'icon' => 'fa-exclamation-triangle'],
        ['level' => 1, 'title' => 'Normal / Regular', 'bg' => 'bg-secondary', 'icon' => 'fa-list-ul']
    ];

    // 2. THE FIX: Filter AND Sort by Date/Time (Ignoring Priority)
    $archiveDocs = $documents->filter(function($doc) use ($currUser, $isAdminOrRecords, $filter) {
        $isFinished = ($doc->status == 'accepted');
        $isSharedToMe = $doc->logs->where('office_id', $currUser->office_id)->where('action', 'DISSEMINATED')->count() > 0;
        $isSharedSystemWide = $isAdminOrRecords && ($doc->logs->where('action', 'DISSEMINATED')->count() > 0);

        if ($filter == 'shared') return $isSharedToMe || $isSharedSystemWide;
        if ($filter == 'accepted') return $isFinished;
        
        return $isFinished || $isSharedToMe || $isSharedSystemWide;
    })->sortByDesc('created_at'); // <--- THIS FORCES THE DATE/TIME SEQUENCE
@endphp
    {{-- SECTION A: PRIORITY TABLES (Only for Active/Transit/All view) --}}
    @if(!$filter || $filter == 'pending' || $filter == 'records')
        @foreach($priorities as $prio)
            @php $prioDocs = $documents->where('priority', $prio['level'])->where('status', 'pending'); @endphp
            @if($prioDocs->count() > 0)
                <div class="card shadow-sm border-0 mb-5" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header {{ $prio['bg'] }} py-3 px-4 d-flex justify-content-between align-items-center text-white">
                        <h5 class="mb-0 fw-bold text-uppercase tracking-wider"><i class="fa {{ $prio['icon'] }} me-2"></i> {{ $prio['title'] }} (In Transit)</h5>
                        <span class="badge bg-white text-dark rounded-pill px-3">{{ $prioDocs->count() }} Items</span>
                    </div>
                    <div class="card-body p-0">
                        @include('documents.partials.table_content', [
                            'tableDocs' => $prioDocs, 
                            'style' => 'maroon', 
                            'showRecipients' => false
                        ])
                    </div>
                </div>
            @endif
        @endforeach
    @endif

    {{-- SECTION B: CONSOLIDATED TABLE (Finished & Shared) --}}
    @if(!$filter || $filter == 'accepted' || $filter == 'shared' || $filter == 'records')
        @if($archiveDocs->count() > 0)
            <div class="card shadow-sm border-0 mb-5" style="border-radius: 12px; overflow: hidden;">
                @php
                    $hColor = ($filter == 'shared') ? 'bg-primary' : (($filter == 'accepted') ? 'bg-success' : 'bg-dark');
                @endphp
                <div class="card-header {{ $hColor }} text-white py-4 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-uppercase tracking-wider">
                        <i class="fa {{ ($filter == 'shared') ? 'fa-share-nodes' : 'fa-archive' }} me-2"></i> 
                        {{ ($filter == 'shared') ? 'System Dissemination Log' : 'Finished & Shared Records' }}
                    </h5>
                    
                    <form action="{{ route('dashboard') }}" method="GET" class="mt-2 mt-lg-0">
                        @if($filter) <input type="hidden" name="filter" value="{{ $filter }}"> @endif
                        <div class="input-group shadow-sm" style="width: 400px;">
                            <input type="text" name="search" class="form-control border-0" placeholder="Search archive..." value="{{ request('search') }}">
                            <button class="btn btn-maroon px-3" type="submit"><i class="fa fa-search"></i></button>
                        </div>
                    </form>
                </div>
                <div class="card-body p-0">
                    @include('documents.partials.table_content', [
                        'tableDocs' => $archiveDocs, 
                        'style' => 'dark', 
                        'showRecipients' => ($isAdminOrRecords && $filter == 'shared')
                    ])
                </div>
            </div>
        @endif
    @endif

    @if($documents->count() == 0)
        <div class="text-center py-5 bg-white rounded-3 shadow-sm mb-5 border border-dashed">
            <i class="fa fa-folder-open fa-5x text-muted opacity-10 mb-3"></i>
            <h4 class="text-muted">No documents found.</h4>
        </div>
    @endif

    <!-- PAGINATION -->
    @if(method_exists($documents, 'hasPages') && $documents->hasPages())
        <div class="d-flex justify-content-center pb-5">{{ $documents->links() }}</div>
    @endif
</div>
@endsection