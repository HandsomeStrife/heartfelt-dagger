<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Domain\Campaign\Models\Campaign;
use Illuminate\Console\Command;

class PopulateCampaignCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaigns:populate-codes {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate campaign_code for existing campaigns that don\'t have one';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        
        $campaigns = Campaign::whereNull('campaign_code')->get();
        
        if ($campaigns->isEmpty()) {
            $this->info('No campaigns found without campaign codes.');
            return Command::SUCCESS;
        }
        
        $this->info(sprintf('Found %d campaigns without campaign codes.', $campaigns->count()));
        
        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
            foreach ($campaigns as $campaign) {
                $code = Campaign::generateUniqueCampaignCode();
                $this->line(sprintf('Would set campaign_code for "%s" (ID: %d) to: %s', $campaign->name, $campaign->id, $code));
            }
            return Command::SUCCESS;
        }
        
        $updated = 0;
        foreach ($campaigns as $campaign) {
            $campaign->campaign_code = Campaign::generateUniqueCampaignCode();
            $campaign->save();
            $updated++;
            
            $this->line(sprintf('Updated campaign "%s" with code: %s', $campaign->name, $campaign->campaign_code));
        }
        
        $this->info(sprintf('Successfully updated %d campaigns with campaign codes.', $updated));
        
        return Command::SUCCESS;
    }
}
