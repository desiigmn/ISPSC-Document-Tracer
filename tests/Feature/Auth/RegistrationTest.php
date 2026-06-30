<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\Office;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the public registration route is strictly disabled.
     */
    public function test_registration_screen_cannot_be_accessed(): void
    {
        $response = $this->get('/register');
        
        // It should return 404 Not Found now that we commented it out in auth.php
        $response->assertStatus(404); 
    }

    /**
     * Test that an Admin can create new personnel via the Staff Management feature.
     */
    public function test_new_personnel_can_be_created_by_admin(): void
    {
        // 1. Setup: Create the target Office
        $office = Office::create([
            'id' => 'ISPSC-MC-GEN-2026-OARLXX',
            'office_name' => 'General Services'
        ]);

        // 2. Setup: Create an Admin assigned to the Records Office
        $admin = User::factory()->create([
            'role' => 'superadmin',
            'email' => 'admin@ispsc.edu.ph',
            'office_id' => 'ISPSC-MC-REC-2026-4URQGK' 
        ]);

        // 3. Action: Admin adds a new staff member
        $response = $this->actingAs($admin)->post(route('admin.staff.store'), [
            'full_name' => 'Lynmuel Morilla',
            'email' => 'staff@ispsc.edu.ph',
            'office_id' => $office->id,
            'campus_code' => 'MC',
            'role' => 'staff',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_title' => 'Director'
        ]);

        // 4. Assertions
        $this->assertDatabaseHas('users', [
            'username' => 'Lynmuel Morilla',
            'email' => 'staff@ispsc.edu.ph',
            'office_id' => 'ISPSC-MC-GEN-2026-OARLXX'
        ]);
        
        $response->assertStatus(302); // Redirects back to personnel list
    }
}