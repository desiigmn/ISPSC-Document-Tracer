<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    protected $fillable = [
        'tracking_id',
        'title',
        'classification',
        'priority',
        'status',
        'uploader_id',
        'current_office_id',
        'target_office_id',
        'file_path',
        'current_step',
        'qr_x',    // Add this
        'qr_y',    // Add this
        'qr_page', // Add this
];

    protected $casts = [
    'is_hard_copy' => 'boolean', // This forces 1 to become true and 0 to become false
];

    /**
     * Relationship to the Multi-upload Attachments
     * FIXED: Added this to resolve the RelationNotFoundException
     */
    public function attachments()
{
    return $this->hasMany(DocumentAttachment::class, 'document_id');
}

    public function currentOffice(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'current_office_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(DocumentLog::class, 'document_id');
    }

    public function signatories(): HasMany
    {
        return $this->hasMany(Signatory::class, 'document_id');
    }

    /**
     * Helper to check if the current user is the current signer
     */
    public function signatoryOrderFor($docId)
    {
        $sig = \App\Models\Signatory::where('document_id', $docId)
                    ->where('user_id', \Illuminate\Support\Facades\Auth::id())
                    ->first();
        return $sig ? $sig->sign_order : 0;
    }

    /**
     * Check if document has been pending for more than 8 hours
     */
    public function isStale()
    {
        return $this->status == 'pending' && $this->updated_at->diffInHours(now()) >= 8;
    }

    public function targetOffice()
{
    // This connects target_office_id to the Office model
    return $this->belongsTo(Office::class, 'target_office_id');
}
public function currentHolder()
{
    // Find the signatory record that matches the current step
    $step = $this->signatories->where('sign_order', $this->current_step)->first();
    
    if ($this->status == 'accepted') {
        return 'Completed / Archived';
    }

    if ($this->status == 'returned') {
        return 'Uploader (For Correction)';
    }

    return $step->office->office_name ?? 'Records Office';
}
}