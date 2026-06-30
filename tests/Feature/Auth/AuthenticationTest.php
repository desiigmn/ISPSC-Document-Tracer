<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'email' => 'testuser@ispsc.edu.ph'
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        
        // FIX: Changed from /dashboard to /
        $response->assertStatus(302);
        $response->assertRedirect('/'); 
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'testuser@ispsc.edu.ph'
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create([
            'email' => 'testuser@ispsc.edu.ph'
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();

        // After logout, the system should redirect to the home/login page
        $response->assertStatus(302);
        $response->assertRedirect('/');
    }
}