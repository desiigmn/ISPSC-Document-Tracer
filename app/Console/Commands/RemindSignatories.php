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
        }
    }
}
}
