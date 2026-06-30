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

        // Start the query with necessary relationships
        $query = Document::query()->with(['uploader', 'logs', 'signatories.office', 'signatories.user']);

        // 2. UNIFIED ACCESS CONTROL (OFFICE-BASED)
        if (!$isAdminOrRecords) {
            $query->where(function($q) use ($user) {
                // Scenario A: I am the Creator
                $q->where('uploader_id', $user->id)

                // Scenario B: Routing - My Office is in the chain 
                // (Changed from user_id to office_id so all staff in the office can see it)
                ->orWhereHas('signatories', function($sig) use ($user) {
                    $sig->where('office_id', $user->office_id);
                })

                // Scenario C: Officially shared with my office via logs
                ->orWhereHas('logs', function($sub) use ($user) {
                    $sub->where('office_id', $user->office_id)
                        ->where('action', 'DISSEMINATED');
                });
            });
        }

        // Clone before filtering/searching for accurate counts
        $baseQueryForStats = clone $query;

        // 3. SEARCH & FILTERS
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('tracking_id', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('title', 'LIKE', '%' . $request->search . '%');
            });
        }

        // 4. TAB FILTERS
        if ($request->filter == 'review') {
            $query->where('status', 'needs_review');
        } elseif ($request->filter == 'pending') {
            $query->whereIn('status', ['pending', 'returned', 'mapping']);
        } elseif ($request->filter == 'accepted') {
            $query->where('status', 'accepted');
        } elseif ($request->filter == 'shared') {
            $query->whereHas('logs', function($q) use ($user) {
                $q->where('action', 'DISSEMINATED')->where('office_id', $user->office_id);
            });
        }

        // 5. EXECUTE QUERY WITH SORTING
        $documents = $query->orderByRaw("FIELD(status, 'mapping', 'needs_review', 'returned', 'pending') ASC")
                           ->orderBy('priority', 'desc')
                           ->orderBy('created_at', 'desc')
                           ->paginate(40)
                           ->appends($request->all());

        // 6. CALCULATE COUNTS FOR CARDS (BASED ON OFFICE)
        
        // A. For Review (Only for Admin/Records)
        $countReview = (clone $baseQueryForStats)->where('status', 'needs_review')->count();

        // B. On Process (What is specifically at the USER'S office right now)
        if ($isAdminOrRecords) {
            $countPending = (clone $baseQueryForStats)->whereIn('status', ['mapping', 'pending', 'returned'])->count();
        } else {
            // For staff: Count documents where status is pending AND current step is THEIR office
            $countPending = (clone $baseQueryForStats)->where('status', 'pending')
                ->whereHas('signatories', function($q) use ($user) {
                    $q->where('office_id', $user->office_id)
                      ->whereColumn('sign_order', 'documents.current_step');
                })->count();
        }

        // C. Shared Copies
        $countShared = (clone $baseQueryForStats)->whereHas('logs', function($sub) use ($user) {
            $sub->where('action', 'DISSEMINATED')->where('office_id', $user->office_id);
        })->count();

        // D. Finished/Accepted
        $countFinished = (clone $baseQueryForStats)->where('status', 'accepted')->count();

        // E. Returned (If user is the uploader and needs to fix something)
        $countReturned = (clone $baseQueryForStats)->where('status', 'returned')
                            ->where('uploader_id', $user->id)
                            ->count();

        return view('dashboard', compact(
            'documents', 
            'countReview', 
            'countPending', 
            'countFinished', 
            'countShared', 
            'countReturned',
            'isAdminOrRecords'
        ));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'username' => 'required|string|max:255',
            'avatar'   => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user->username = $request->username;

        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        $user->save();
        return back()->with('msg', 'Profile successfully updated!');
    }
}