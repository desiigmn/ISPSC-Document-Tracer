<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RemindSignatories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:remind-signatories';

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
    $staleDocs = Document::where('status', 'pending')->get();
    foreach($staleDocs as $doc) {
        if($doc->updated_at->diffInHours(now()) >= 8) {
            // Find current signatory
            $currentSig = Signatory::where('document_id', $doc->id)
                ->where('sign_order', $doc->current_step)
                ->first();
            
            if($currentSig) {
                Notification::create([
                    'user_id' => $currentSig->user_id,
                    'type' => 'stale',
                    'message' => "REMINDER: Document {$doc->tracking_id} has been waiting for 8 hours.",
                    'link' => route('documents.view', $doc->id)
                ]);
            }
        }
    }
}
}
