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

    // 1. ACCESS CONTROL
    $recordsOfficeId = 'ISPSC-MC-REC-2026-4URQGK';
    $isSuperAdmin = ($user->role === 'superadmin');
    $isRecordsStaff = ($user->office_id === $recordsOfficeId);

    if (!$isSuperAdmin && !$isRecordsStaff) {
        $query->where(function($q) use ($user) {
            $q->where('uploader_id', $user->id)
              ->orWhereHas('signatories', function($sub) use ($user) {
                  $sub->where('user_id', $user->id);
              })
              ->orWhereHas('logs', function($sub) use ($user) {
                  $sub->where('office_id', $user->office_id)
                      ->where('action', 'DISSEMINATED');
              });
        });
    }

    $baseQuery = clone $query;

    // 2. SEARCH
    if ($request->filled('search')) {
        $query->where('tracking_id', 'LIKE', '%' . $request->search . '%')
              ->orWhere('title', 'LIKE', '%' . $request->search . '%');
    }

    // 3. FETCH PAGINATED RESULTS (30 per page)
    // We sort by Priority so Extreme/Urgent appear first in the collection
    $documents = $query->orderBy('priority', 'desc')->latest()->paginate(30)->appends($request->all());

    // 4. COUNTS FOR CARDS
    $countTotal = $baseQuery->count();
    $countPending = (clone $baseQuery)->where('status', 'pending')->count();
    $countFinished = (clone $baseQuery)->where('status', 'accepted')->count();
    $countShared = (clone $baseQuery)->whereHas('logs', function($sub) use ($user) {
        $sub->where('office_id', $user->office_id)->where('action', 'DISSEMINATED');
    })->count();

    return view('dashboard', compact('documents', 'countTotal', 'countPending', 'countFinished', 'countShared', 'user'));
}
}