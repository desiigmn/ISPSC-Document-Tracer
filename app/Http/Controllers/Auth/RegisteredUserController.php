<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Office;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        // Fetch all offices so they appear in the dropdown
        $offices = Office::orderBy('office_name', 'asc')->get(); 
        
        return view('auth.register', compact('offices'));
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Validate the input
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string'],
            'office_id' => ['required_unless:role,superadmin', 'nullable', 'string', 'max:50'],
            'campus_code' => ['required_unless:role,superadmin', 'nullable', 'string'], 
            'admin_passkey' => ['nullable', 'string'],
        ]);

        $role = $request->role;
        $office_id = $request->office_id;
        $campus_code = $request->campus_code;

        // 2. ISPSC SECURITY CHECK: Verify the secret passkey for Super Admin
        if ($role === 'superadmin') {
            if ($request->admin_passkey !== 'ISPSC_ADMIN_2024') {
                throw ValidationException::withMessages([
                    'admin_passkey' => 'Invalid Passkey! You do not have authority to register as a Super Admin.',
                ]);
            }
            $office_id = null; 
            $campus_code = null; 
        }

        // 3. Create the User
        // Note: Using 'username' because that is your database column name
        $user = User::create([
            'username' => $request->name, 
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
            'office_id' => $office_id,
            'campus_code' => $campus_code,
        ]);

        // 4. Trigger standard Laravel events
        event(new Registered($user));

        // 5. Log the user in
        Auth::login($user);

        // 6. Redirect to dashboard
        return redirect(route('dashboard', absolute: false));
    }
}