<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;

class PublicTracerController extends Controller
{
    public function index(Request $request)
    {
        $tid = $request->get('tid'); // Get Tracking ID from URL
        $document = null;

        if ($tid) {
            $document = Document::with(['logs.user', 'currentOffice'])
                ->where('tracking_id', $tid)
                ->first();
        }

        return view('public.trace', compact('document'));
    }
}