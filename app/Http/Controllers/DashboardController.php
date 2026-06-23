<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function index(Request $request) 
    {
        $user = Auth::user();
        
        // 1. ROLE IDENTIFICATION
        $isRecordsOffice = str_contains($user->office_id ?? '', '-REC-');
        $isAdminOrRecords = ($user->role === 'superadmin' || $isRecordsOffice);

        $query = Document::query()->with(['uploader', 'targetOffice', 'logs', 'signatories.user']);

        // 2. UNIFIED ACCESS CONTROL
        if (!$isAdminOrRecords) {
            $query->where(function($q) use ($user) {
                // Scenario A: I am the Creator
                $q->where('uploader_id', $user->id)

                // Scenario B: I am a Signatory (Hide if status is Mapping or Needs Review)
                ->orWhere(function($sub) use ($user) {
                    $sub->whereHas('signatories', function($sig) use ($user) {
                        $sig->where('user_id', $user->id)
                            ->whereRaw('sign_order <= documents.current_step');
                    })->whereNotIn('status', ['mapping', 'needs_review']);
                })

                // Scenario C: Officially shared with my office
                ->orWhereHas('logs', function($sub) use ($user) {
                    $sub->where('office_id', $user->office_id)
                        ->where('action', 'DISSEMINATED');
                });
            });
        }

        // Clone before filtering/searching for accurate global counts
        $baseQuery = clone $query;

        // 3. SEARCH & FILTERS
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('tracking_id', 'LIKE', '%' . $request->search . '%')
                ->orWhere('title', 'LIKE', '%' . $request->search . '%');
            });
        }

        if ($request->filter == 'review') {
            $query->where('status', 'needs_review');
        } elseif ($request->filter == 'pending') {
            $query->whereIn('status', ['pending', 'returned', 'mapping']);
        } elseif ($request->filter == 'accepted') {
            $query->where('status', 'accepted');
        } elseif ($request->filter == 'shared') {
            $query->whereHas('logs', function($q) use ($user, $isAdminOrRecords) {
                $q->where('action', 'DISSEMINATED');
                // If not admin, restrict to their specific office
                if (!$isAdminOrRecords) {
                    $q->where('office_id', $user->office_id);
                }
            });
        }


        // 4. UNIFIED SEQUENTIAL SORTING
        // Logic: Mapping/Review -> Priority 3 -> Priority 2 -> Priority 1 -> Accepted
        $documents = $query->orderByRaw("FIELD(status, 'mapping', 'needs_review', 'returned', 'pending') ASC")
                           ->orderBy('priority', 'desc')
                           ->orderBy('created_at', 'desc')
                           ->paginate(40)
                           ->appends($request->all());

        // 5. CALCULATE COUNTS FOR CARDS
        $countReview = (clone $baseQuery)->where('status', 'needs_review')->count();
        $countPending = (clone $baseQuery)->whereIn('status', ['mapping', 'pending', 'returned'])->count();
        $countFinished = (clone $baseQuery)->where('status', 'accepted')->count();

        // FIX: Shared Count logic must match the filter logic
        $countShared = (clone $baseQuery)->whereHas('logs', function($sub) use ($user, $isAdminOrRecords) {
            $sub->where('action', 'DISSEMINATED');
            if (!$isAdminOrRecords) {
                $sub->where('office_id', $user->office_id);
            }
        })->count();

        return view('dashboard', compact(
            'documents', 
            'countReview', 
            'countPending', 
            'countFinished', 
            'countShared', 
            'isAdminOrRecords'
        ));
    }
    public function updateProfile(Request $request)
    {
        // 1. Get the currently logged in user (whoever they are)
        $user = Auth::user();

        // 2. Validate Input
        $request->validate([
            'username' => 'required|string|max:255',
            'avatar'   => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // 2MB Max
        ]);

        // 3. Update the name
        $user->username = $request->username;

        // 4. Handle Avatar Upload
        if ($request->hasFile('avatar')) {
            // Delete old photo if it exists to save space
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            
            // Save new photo
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        // 5. Save everything to database
        $user->save();

        return back()->with('msg', 'Profile successfully updated!');
    }
}