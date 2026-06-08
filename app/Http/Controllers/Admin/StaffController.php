<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class StaffController extends Controller
{
    public function index() {
        if (Auth::user()->role !== 'superadmin') abort(403);

    $allUsers = \App\Models\User::with('office')->orderBy('username')->get();
    $offices = \App\Models\Office::orderBy('office_name')->get();

    return view('admin.personnel', compact('allUsers', 'offices'));
    }

public function store(Request $request)
{
    $request->validate([
        'full_name'   => 'required|string|max:255',
        'email'       => 'required|email|unique:users,email',
        'office_id'   => 'required',
        'campus_code' => 'required',
        'role'        => 'required|in:staff,superadmin', // Validate role
        'password'    => 'required|string|min:6|confirmed',
    ]);

    \App\Models\User::create([
        'username'    => $request->full_name,
        'email'       => $request->email,
        'password'    => \Illuminate\Support\Facades\Hash::make($request->password),
        'office_id'   => $request->office_id,
        'campus_code' => $request->campus_code,
        'role'        => $request->role, // Uses the selected role
    ]);

    return back()->with('msg', 'Account created successfully!');
}
public function resetPassword(Request $request, $id)
{
    // Security check
    if (Auth::user()->role !== 'superadmin') abort(403);

    $request->validate([
        'password' => 'required|string|min:6|confirmed',
    ]);

    $user = \App\Models\User::findOrFail($id);
    $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
    $user->save();

    return back()->with('msg', 'Password for ' . $user->username . ' has been reset successfully!');
}
public function destroy($id)
{
    // Security check: Only superadmin can delete
    if (auth()->user()->role !== 'superadmin') abort(403);

    $user = \App\Models\User::findOrFail($id);

    // Safety: Prevent deleting own account
    if ($user->id === auth()->id()) {
        return back()->with('error', 'Security Alert: You cannot delete your own administrator account.');
    }

    $username = $user->username;
    $user->delete();

    return back()->with('msg', "Account for $username has been removed from the system.");
}
    public function changeOwnPassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();

        // Check if current password matches
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The provided password does not match our records.']);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password updated successfully!');
    }
}
