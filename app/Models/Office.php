<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Office extends Model
{
    use HasFactory;

    // Set this to false since your IDs are strings (ISPSC-MC-OFF-...)
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id', 'office_name'];

    /**
     * Define the relationship: An office has many users.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'office_id');
    }
}