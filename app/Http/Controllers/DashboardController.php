<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request) 
    {
        $user = Auth::user();
        $query = Document::query()->with(['uploader', 'targetOffice', 'logs', 'signatories']);

        // 1. ACCESS CONTROL
        $recordsOfficeId = 'ISPSC-MC-REC-2026-4URQGK';
        $isAdminOrRecords = ($user->role === 'superadmin' || $user->office_id === $recordsOfficeId);

        if (!$isAdminOrRecords) {
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

        // Clone the query after access control but before search/filters for accurate counts
        $baseQuery = clone $query;

        // 2. SEARCH LOGIC
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('tracking_id', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('title', 'LIKE', '%' . $request->search . '%');
            });
        }

        // 3. FILTERING LOGIC
        if ($request->filter == 'shared') {
            $query->whereHas('logs', function($q) use ($user, $isAdminOrRecords) {
                $q->where('action', 'DISSEMINATED');
                if (!$isAdminOrRecords) {
                    $q->where('office_id', $user->office_id);
                }
            });
        } elseif ($request->filter == 'accepted') {
            $query->where('status', 'accepted');
        } elseif ($request->filter == 'pending') {
            $query->whereIn('status', ['pending', 'returned']);
        }

        // 4. FETCH RESULTS (Hierarchical by Priority)
        $documents = $query->orderBy('priority', 'desc')
                           ->latest()
                           ->paginate(30)
                           ->appends($request->all());

        // 5. CALCULATE COUNTS FOR CARDS
        $countTotal = $baseQuery->count();
        
        $countPending = (clone $baseQuery)->whereIn('status', ['pending', 'returned'])->count();
        
        $countFinished = (clone $baseQuery)->where('status', 'accepted')->count();
        
        $countShared = (clone $baseQuery)->whereHas('logs', function($sub) use ($user, $isAdminOrRecords) {
            $sub->where('action', 'DISSEMINATED');
            if (!$isAdminOrRecords) {
                $sub->where('office_id', $user->office_id);
            }
        })->count();

        return view('dashboard', compact(
            'documents', 
            'countTotal', 
            'countPending', 
            'countFinished', 
            'countShared', 
            'isAdminOrRecords'
        ));
    }
}