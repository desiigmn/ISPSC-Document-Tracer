<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Document;
use App\Models\Signatory;
use Illuminate\Support\Facades\Mail;
use App\Mail\UrgentDocumentAlert;

class SendPriorityReminders extends Command
{
    protected $signature = 'app:send-reminders';
    protected $description = 'Send recurring email reminders based on priority levels';

    public function handle()
    {
        // 1. Find all documents that are still "In Transit"
        $documents = Document::where('status', 'pending')->get();

        foreach ($documents as $doc) {
            // 2. Find the person whose turn it is to sign
            $currentSigner = Signatory::with('user')
                ->where('document_id', $doc->id)
                ->where('sign_order', $doc->current_step)
                ->where('status', 'pending')
                ->first();

            if (!$currentSigner || !$currentSigner->user->email) continue;

            // 3. Logic: How many hours have passed since the LAST reminder?
            // If they were never reminded, we compare against when they first received the document
            $startTime = $currentSigner->last_reminded_at ?? $currentSigner->updated_at;
            $hoursPassed = $startTime->diffInHours(now());

            $shouldSend = false;

            // Apply your hierarchy rules
            if ($doc->priority == 3 && $hoursPassed >= 3) {
                $shouldSend = true; // Extreme: 3 hours
            } elseif ($doc->priority == 2 && $hoursPassed >= 6) {
                $shouldSend = true; // Urgent: 6 hours
            } elseif ($doc->priority == 1 && $hoursPassed >= 12) {
                $shouldSend = true; // Normal: 12 hours
            }

            if ($shouldSend) {
                try {
                    Mail::to($currentSigner->user->email)->send(new UrgentDocumentAlert($doc, true));
                    
                    // 4. UPDATE timestamp so we don't remind again until the next interval
                    $currentSigner->update(['last_reminded_at' => now()]);

                    $this->info("Reminder sent for doc {$doc->tracking_id} to {$currentSigner->user->username}");
                } catch (\Exception $e) {
                    $this->error("Failed to send reminder: " . $e->getMessage());
                }
            }
        }
    }
}