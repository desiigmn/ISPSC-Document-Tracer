@extends('layouts.ispsc')
@section('title', 'Document Hub')

@push('css')
<style>
    .parcel-tracker { list-style: none; padding: 0; position: relative; }
    .parcel-tracker::before { content: ''; position: absolute; left: 15px; top: 0; bottom: 0; width: 2px; background: #ddd; }
    .tracker-step { position: relative; padding-left: 45px; margin-bottom: 25px; }
    .tracker-icon { position: absolute; left: 0; width: 32px; height: 32px; background: #fff; border: 2px solid #ddd; border-radius: 50%; text-align: center; line-height: 28px; z-index: 2; color: #ccc; }
    .tracker-step.completed .tracker-icon { background: var(--ispsc-maroon); border-color: var(--ispsc-maroon); color: #fff; }
    .tracker-step.active .tracker-icon { border-color: var(--ispsc-maroon); color: var(--ispsc-maroon); box-shadow: 0 0 10px rgba(128,0,0,0.3); }
    #pdf-viewer-container { background: #525659; padding: 20px; border-radius: 8px; text-align: center; }
    #pdf-canvas-wrapper { position: relative; display: inline-block; background: white; box-shadow: 0 0 15px rgba(0,0,0,0.5); }
    canvas { display: block; }
</style>
@endpush

@section('content')
<div class="row">
    <!-- Column 1: Tracker -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">DOCUMENT JOURNEY</div>
            <div class="card-body">
                <ul class="parcel-tracker">
                    <li class="tracker-step completed">
                        <div class="tracker-icon"><i class="fa fa-door-open"></i></div>
                        <div class="tracker-content"><p class="mb-0 fw-bold">TIME OF HELLO</p><small class="text-muted">{{ $document->created_at->format('M d, H:i A') }}</small></div>
                    </li>
                    <!-- Sequential loop for signatories would go here -->
                </ul>
            </div>
        </div>
    </div>

    <!-- Column 2: Hub / PDF -->
    <div class="col-md-6 text-center">
        <div class="card shadow-sm border-0 mb-3 no-print">
            <div class="card-body d-flex justify-content-between py-2">
                <span class="fw-bold">Status: {{ strtoupper($document->status) }}</span>
                <button onclick="window.print()" class="btn btn-sm btn-outline-dark"><i class="fa fa-print"></i> Print Copy</button>
            </div>
        </div>
        <div id="pdf-viewer-container">
            <div id="pdf-canvas-wrapper">
                <canvas id="pdf-render"></canvas>
            </div>
        </div>
    </div>

    <!-- Column 3: Logs -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">TRANSACTION LOGS</div>
            <div class="card-body px-2" style="max-height: 500px; overflow-y: auto;">
                @foreach($document->logs as $log)
                <div class="border-bottom mb-2 pb-2">
                    <small class="text-danger fw-bold text-uppercase" style="font-size:10px;">{{ $log->action }}</small><br>
                    <small class="text-muted" style="font-size:11px;">{{ $log->created_at->format('M d, H:i') }} - {{ $log->user->name }}</small>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection