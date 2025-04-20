<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Campaign;

class UpdateCampaignStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaigns:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update campaign status for expired campaigns (0 days left)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Checking for expired campaigns...');
        
        // Update status kampanye yang sudah lewat deadline
        $updated = Campaign::checkAndUpdateExpiredCampaigns();
        
        $this->info('Campaign status updated successfully!');
        
        return 0;
    }
}