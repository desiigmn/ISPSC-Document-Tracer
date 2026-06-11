<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
// 1. ADD THESE IMPORTS TO FIX THE YELLOW LINES
use App\Models\Document;
use App\Models\Signatory;
use Illuminate\Support\Facades\Mail;
use App\Mail\UrgentDocumentAlert;
use Carbon\Carbon;

class RemindSignatories extends Command
{
    /**
     * The name and signature of the console command.
     * This is what you type in the terminal: php artisan app:remind-signatories
     */
    protected $signature = 'app:remind-signatories';

    /**
     * The console command description.
     */
    protected $description = 'Sends automated reminders based on priority hierarchy';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 2. Find all pending documents
        $documents = Document::where('status', 'pending')->get();

        foreach ($documents as $doc) {
            // 3. Find the current person waiting to sign
            $currentSig = Signatory::with('user')
                ->where('document_id', $doc->id)
                ->where('sign_order', $doc->current_step)
                ->where('status', 'pending')
                ->first();

            // Skip if no one is assigned or they have no email
            if (!$currentSig || !$currentSig->user || !$currentSig->user->email) {
                continue;
            }

            // 4. Calculate time elapsed since last reminder (or since they got the doc)
            $lastTime = $currentSig->last_reminded_at ?? $currentSig->updated_at;
            $hoursPassed = Carbon::parse($lastTime)->diffInHours(now());

            $shouldSend = false;

            // 5. Apply the ISPSC Priority Rules
            if ($doc->priority == 3 && $hoursPassed >= 1) {
                $shouldSend = true; // EXTREME: 1 hour
            } elseif ($doc->priority == 2 && $hoursPassed >= 6) {
                $shouldSend = true; // URGENT: 6 hours
            } elseif ($doc->priority == 1 && $hoursPassed >= 12) {
                $shouldSend = true; // NORMAL: 12 hours
            }

            // 6. Send the Email
            if ($shouldSend) {
                try {
                    Mail::to($currentSig->user->email)->send(new UrgentDocumentAlert($doc, true));
                    
                    // Update timestamp so the countdown starts over
                    $currentSig->update(['last_reminded_at' => now()]);

                    $this->info("Reminder successfully sent for {$doc->tracking_id}");
                } catch (\Exception $e) {
                    $this->error("Failed sending to {$currentSig->user->email}: " . $e->getMessage());
                }
            }
        }

        return Command::SUCCESS;
    }
}