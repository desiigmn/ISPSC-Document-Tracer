<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
    'username', // Add this
    'email',
    'password',
    'role',     // Ensure this is here
    'campus_code', // Ensure this is here
    'office_id', // Ensure this is here
];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relationship to the Office model.
     */
    /**
 * Define the relationship: A user belongs to one office.
 */
public function office()
{
    return $this->belongsTo(Office::class, 'office_id');
}

    /**
     * Helper to check if user is a super admin
     */
    // Add this helper method
public function isSuperAdmin()
{
    // Assuming you have a 'role' column in your users table
    return $this->role === 'superadmin'; 
}

    /**
 * Helper to find what order this user is supposed to sign a specific document.
 */
public function signatoryOrderFor($documentId)
{
    $signatory = \App\Models\Signatory::where('document_id', $documentId)
                    ->where('user_id', $this->id)
                    ->first();

    return $signatory ? $signatory->sign_order : 0;
}
// In Office Model:
public function users() {
    return $this->hasMany(User::class, 'office_id');
}

}