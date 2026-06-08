<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendUrgentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-urgent-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
public function handle()
{
    // Find all pending documents
    $documents = \App\Models\Document::where('status', 'pending')->get();

    foreach ($documents as $doc) {
        // Get the person who currently needs to sign
        $currentSignatory = $doc->signatories()
            ->where('sign_order', $doc->current_step)
            ->where('status', 'pending')
            ->first();

        if (!$currentSignatory) continue;

        $daysElapsed = $currentSignatory->updated_at->diffInDays(now());
        $shouldRemind = false;

        // Logic for deadlines
        if ($doc->priority == 3 && $daysElapsed >= 1) {
            $shouldRemind = true; // Extremely Urgent: 1 Day
        } elseif ($doc->priority == 2 && $daysElapsed >= 3) {
            $shouldRemind = true; // Urgent: 3 Days
        }

        if ($shouldRemind) {
            \Illuminate\Support\Facades\Mail::to($currentSignatory->user->email)
                ->send(new \App\Mail\UrgentDocumentAlert($doc, true));
            
            // Log the reminder in the Audit Trail
            $doc->logs()->create([
                'user_id' => $currentSignatory->user_id,
                'action' => 'SYSTEM REMINDER',
                'office_id' => 'SYSTEM',
                'remarks' => "Automatic email reminder sent to {$currentSignatory->user->username} (Priority Level: {$doc->priority})"
            ]);
        }
    }
}
}
