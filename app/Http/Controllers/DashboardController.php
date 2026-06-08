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
        $query = Document::query();

        // 1. ACCESS CONTROL LOGIC (Define what the user is allowed to see)
        $recordsOfficeId = 'ISPSC-MC-REC-2026-4URQGK';
        $isSuperAdmin = ($user->role === 'superadmin');
        $isRecordsStaff = ($user->office_id === $recordsOfficeId);

        if (!$isSuperAdmin && !$isRecordsStaff) {
            $query->where(function($q) use ($user) {
                $q->where('uploader_id', $user->id)
                  ->orWhereHas('signatories', function($sub) use ($user) {
                      $sub->where('user_id', $user->id);
                  });
            });
        }

        // Clone the base query to calculate card counts before applying specific search/filters
        $baseQuery = clone $query;

        // 2. SEARCH LOGIC
        if ($request->filled('search')) {
            $query->where('tracking_id', 'LIKE', '%' . $request->search . '%');
        }

        // 3. STATUS FILTER LOGIC (From clicking Dashboard cards)
        if ($request->has('filter')) {
            if ($request->filter == 'pending') {
                $query->where('status', 'pending');
            } elseif ($request->filter == 'accepted') {
                $query->where('status', 'accepted');
            }
        }

        // 4. FETCH RESULTS WITH PRIORITY HIERARCHY
        // Priority 3 (Extremely Urgent) will be at the very top, followed by 2, then 1.
        $documents = $query->orderBy('priority', 'desc') 
                           ->latest() 
                           ->paginate(30)
                           ->appends($request->all());

        // 5. GET COUNTS FOR THE TOP CARDS
        // These reflect the user's total accessible documents regardless of the current search
        $countTotal = $baseQuery->count();
        $countPending = (clone $baseQuery)->where('status', 'pending')->count();
        $countFinished = (clone $baseQuery)->where('status', 'accepted')->count();

        // 6. RETURN VIEW
        return view('dashboard', [
            'documents'     => $documents,
            'countTotal'    => $countTotal,
            'countPending'  => $countPending,
            'countFinished' => $countFinished,
        ]);
    }
}