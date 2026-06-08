<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentLog extends Model
{
    // Ensure these match your database columns from the SQL dump
    protected $fillable = [
        'document_id', 
        'user_id', 
        'action', 
        'remarks', 
        'office_id'
    ];

    /**
     * Define the relationship to the User who performed the action.
     */
    public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}

    /**
     * Relationship to the Document.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

        public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }
}