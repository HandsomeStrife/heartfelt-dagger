<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Domain\Room\Services\GoogleDriveService;
use Illuminate\Console\Command;

class TestGoogleDriveOAuth extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:google-drive-oauth';

    /**
     * The console command description.
     */
    protected $description = 'Test Google Drive OAuth configuration';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Testing Google Drive OAuth Configuration...');
        $this->newLine();

        // Display current configuration
        $this->info('Current Configuration:');
        $this->line('Client ID: ' . config('services.google_drive.client_id'));
        $this->line('Client Secret: ' . (config('services.google_drive.client_secret') ? '[SET]' : '[NOT SET]'));
        $this->line('Redirect URI: ' . config('services.google_drive.redirect_uri'));
        $this->newLine();

        try {
            // Generate authorization URL
            $authUrl = GoogleDriveService::getAuthorizationUrl();
            
            $this->info('✅ OAuth URL generated successfully!');
            $this->newLine();
            $this->info('Authorization URL:');
            $this->line($authUrl);
            $this->newLine();
            
            $this->warn('Next steps:');
            $this->line('1. Copy the URL above and paste it in your browser');
            $this->line('2. Complete the OAuth flow');
            $this->line('3. If you get a 403 error, check your Google Cloud Console configuration');
            $this->newLine();
            
            $this->info('Google Cloud Console Checklist:');
            $this->line('□ OAuth 2.0 Client ID exists');
            $this->line('□ Authorized redirect URI includes: ' . config('services.google_drive.redirect_uri'));
            $this->line('□ Google Drive API is enabled');
            $this->line('□ OAuth consent screen is configured');
            $this->line('□ Your Google account is added as test user (if in testing mode)');
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to generate OAuth URL: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
