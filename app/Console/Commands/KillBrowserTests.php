<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

class KillBrowserTests extends Command
{
    protected $signature = 'browser:kill {--force : Force kill all browser processes}';

    protected $description = 'Kill hanging browser test processes';

    public function handle(): int
    {
        $this->info('🔍 Checking for hanging browser test processes...');
        
        // Check for processes inside the container
        $containerProcesses = shell_exec('docker exec -i daggerheart-laravel.test-1 ps aux | grep -E "(pest|chrome|playwright|node.*test)" | grep -v grep') ?? '';
        
        if (!empty($containerProcesses)) {
            $this->warn('Found processes in container:');
            $this->line($containerProcesses);
            
            if ($this->option('force') || $this->confirm('Kill these processes?')) {
                shell_exec('docker exec -i daggerheart-laravel.test-1 pkill -f "pest|chrome|playwright"');
                $this->info('✅ Killed container processes');
            }
        } else {
            $this->info('✅ No hanging processes found in container');
        }
        
        // Check for processes on host
        $hostProcesses = shell_exec('ps aux | grep -E "sail.*pest|vendor.*pest" | grep -v grep') ?? '';
        
        if (!empty($hostProcesses)) {
            $this->warn('Found processes on host:');
            $this->line($hostProcesses);
            
            if ($this->option('force') || $this->confirm('Kill these processes?')) {
                shell_exec('pkill -f "sail.*pest"');
                shell_exec('pkill -f "vendor.*pest"');
                $this->info('✅ Killed host processes');
            }
        } else {
            $this->info('✅ No hanging processes found on host');
        }
        
        // Clean up any orphaned Chrome processes
        $chromeProcesses = shell_exec('docker exec -i daggerheart-laravel.test-1 ps aux | grep chrome | grep -v grep') ?? '';
        if (!empty($chromeProcesses)) {
            $this->info('🧹 Cleaning up Chrome processes...');
            shell_exec('docker exec -i daggerheart-laravel.test-1 pkill -f chrome');
        }
        
        $this->info('🎯 Browser test cleanup complete!');
        return 0;
    }
}
