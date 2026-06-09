<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Office;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
public function index(Request $request) 
{
    $user = Auth::user();
    $query = Document::query()->with(['uploader', 'targetOffice', 'logs']);

    // 1. Identify Roles
    $recordsOfficeId = 'ISPSC-MC-REC-2026-4URQGK';
    $isAdminOrRecords = ($user->role === 'superadmin' || $user->office_id === $recordsOfficeId);

    // 2. Access Control (Regular staff only see their own/involved/disseminated docs)
    if (!$isAdminOrRecords) {
        $query->where(function($q) use ($user) {
            $q->where('uploader_id', $user->id)
              ->orWhereHas('signatories', function($sub) use ($user) {
                  $sub->where('user_id', $user->id);
              })
              ->orWhereHas('logs', function($sub) use ($user) {
                  $sub->where('office_id', $user->office_id)->where('action', 'DISSEMINATED');
              });
        });
    }

    // 3. Clone for counts (Keep card numbers accurate)
    $baseQuery = clone $query;

    // 4. THE FIX: Filtering Logic
    if ($request->filter == 'accepted') {
        $query->where('status', 'accepted');
    } elseif ($request->filter == 'pending') {
        $query->where('status', 'pending');
    } elseif ($request->filter == 'shared') {
        $query->whereHas('logs', function($q) use ($user, $isAdminOrRecords) {
            $q->where('action', 'DISSEMINATED');
            if (!$isAdminOrRecords) $q->where('office_id', $user->office_id);
        });
    } else {
        // DEFAULT VIEW: 
        // For Admins: Get both pending and accepted so they both show in separate tables
        // For Staff: Just show pending
        if (!$isAdminOrRecords) {
            $query->where('status', 'pending');
        }
    }

    // 5. Fetch Final Results
    $documents = $query->orderBy('priority', 'desc')->latest()->paginate(30)->appends($request->all());

    // 6. Calculate Counts
    $countTotal = $baseQuery->count();
    $countPending = (clone $baseQuery)->where('status', 'pending')->count();
    $countFinished = (clone $baseQuery)->where('status', 'accepted')->count();
    $countShared = (clone $baseQuery)->whereHas('logs', function($q) use ($user, $isAdminOrRecords) {
        $q->where('action', 'DISSEMINATED');
        if (!$isAdminOrRecords) $q->where('office_id', $user->office_id);
    })->count();

    return view('dashboard', compact('documents', 'countTotal', 'countPending', 'countFinished', 'countShared'));
}
}