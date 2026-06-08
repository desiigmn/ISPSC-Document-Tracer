<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Office;
use Illuminate\Http\Request;

class OfficeController extends Controller
{
    // REMOVE THE __construct() block that was causing the error

    public function index() {
        $offices = Office::orderBy('office_name')->get();
        // Since we are now in the Personnel page, this might not even be needed 
        // if you are doing everything inside the StaffController index.
        return view('admin.personnel', compact('offices'));
    }

    public function store(Request $request) {
        $request->validate(['office_name' => 'required|unique:offices,office_name']);
        
        $id = "ISPSC-MC-OFF-" . date('Y') . "-" . strtoupper(substr(md5(uniqid()), 0, 6));

        Office::create([
            'id' => $id,
            'office_name' => $request->office_name
        ]);

        return back()->with('msg', 'Office added successfully!');
    }

    public function destroy($id) {
        $office = Office::findOrFail($id);
        
        // Safety Check: Don't delete if users are still in this office
        if($office->users()->count() > 0) {
            return back()->with('error', 'Cannot remove office. Staff members are still assigned to it.');
        }

        $office->delete();
        return back()->with('msg', 'Office removed.');
    }
}