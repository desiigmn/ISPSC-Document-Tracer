<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Document;
use App\Models\Signatory;
use Illuminate\Support\Facades\Mail;
use App\Mail\UrgentDocumentAlert;
use Carbon\Carbon;

class SendPriorityReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:send-reminders';

    /**
     * The console command description.
     */
    protected $description = 'Send recurring email reminders based on priority levels (Extreme: 1hr, Urgent: 6hr, Normal: 12hr)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 1. Find all documents that are currently being tracked
        $documents = Document::where('status', 'pending')->get();

        foreach ($documents as $doc) {
            // 2. Find the specific signatory whose turn it is to sign
            $currentSigner = Signatory::with('user')
                ->where('document_id', $doc->id)
                ->where('sign_order', $doc->current_step)
                ->where('status', 'pending')
                ->first();

            // Skip if no one needs to sign or user has no email
            if (!$currentSigner || !$currentSigner->user || !$currentSigner->user->email) {
                continue;
            }

            // 3. Logic: Determine time since last reminder or last update
            // We use last_reminded_at if it exists; otherwise, the time they first got the doc
            $lastTime = $currentSigner->last_reminded_at ?? $currentSigner->updated_at;
            $hoursElapsed = \Carbon\Carbon::parse($lastTime)->diffInHours(now());

            $shouldSend = false;

            // 4. HIERARCHY RULES:
            if ($doc->priority == 3 && $hoursElapsed >= 1) { // 1 Hour for Extreme
                $shouldSend = true; // Extreme: EVERY 1 HOUR
            } elseif ($doc->priority == 2 && $hoursElapsed >= 3) {
                $shouldSend = true; // Urgent: Every 6 hours
            } elseif ($doc->priority == 1 && $hoursElapsed >= 8) {
                $shouldSend = true; // Normal: Every 12 hours
            }

            // 5. Send the Alert
            if ($shouldSend) {
                try {
                    // Send using the Mailable we fixed (Document, isReminder)
                    Mail::to($currentSigner->user->email)->send(new UrgentDocumentAlert($doc, true));
                    
                    // UPDATE: Set current time as the new reminder timestamp
                    $currentSigner->update(['last_reminded_at' => now()]);

                    $this->info("Success: Reminder sent to {$currentSigner->user->username} for Doc ID: {$doc->tracking_id}");
                } catch (\Exception $e) {
                    $this->error("Failed to send reminder for {$doc->tracking_id}: " . $e->getMessage());
                }
            }
        }

        return Command::SUCCESS;
    }
}