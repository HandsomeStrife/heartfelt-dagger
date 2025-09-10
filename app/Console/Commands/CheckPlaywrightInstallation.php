<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckPlaywrightInstallation extends Command
{
    protected $signature = 'playwright:check';

    protected $description = 'Check Playwright installation status and browser availability';

    public function handle(): int
    {
        $this->info('🔍 Checking Playwright Installation Status...');

        // Check if playwright binary exists
        $this->checkPlaywrightBinary();

        // Check installed browsers
        $this->checkInstalledBrowsers();

        // Check Node.js and npm
        $this->checkNodeEnvironment();

        // Check browser dependencies
        $this->checkBrowserDependencies();

        // Check permissions
        $this->checkPermissions();

        return 0;
    }

    private function checkPlaywrightBinary(): void
    {
        $this->info("\n📦 Checking Playwright Binary...");

        // Check if npx playwright exists
        $result = shell_exec('which npx 2>/dev/null');
        if (! $result) {
            $this->error('❌ npx command not found');

            return;
        }
        $this->line('✅ npx found at: '.trim($result));

        // Check playwright availability
        $playwrightCheck = shell_exec('npx playwright --version 2>/dev/null');
        if (! $playwrightCheck) {
            $this->error('❌ Playwright not available via npx');

            return;
        }
        $this->line('✅ Playwright version: '.trim($playwrightCheck));
    }

    private function checkInstalledBrowsers(): void
    {
        $this->info("\n🌐 Checking Installed Browsers...");

        // Check Playwright browser installation directory
        $homeDir = $_SERVER['HOME'] ?? '/home/sail';
        $playwrightDir = $homeDir.'/.cache/ms-playwright';

        if (! is_dir($playwrightDir)) {
            $this->error("❌ Playwright browsers directory not found: {$playwrightDir}");

            return;
        }

        $this->line("✅ Playwright browsers directory exists: {$playwrightDir}");

        // List installed browsers
        $browsers = glob($playwrightDir.'/chromium-*');
        if (empty($browsers)) {
            $this->error('❌ No Chromium browsers found');
        } else {
            foreach ($browsers as $browser) {
                $browserName = basename($browser);
                $this->line("✅ Found browser: {$browserName}");
            }
        }

        // Check for other browsers
        $firefoxBrowsers = glob($playwrightDir.'/firefox-*');
        if (! empty($firefoxBrowsers)) {
            foreach ($firefoxBrowsers as $browser) {
                $browserName = basename($browser);
                $this->line("✅ Found Firefox: {$browserName}");
            }
        }

        $webkitBrowsers = glob($playwrightDir.'/webkit-*');
        if (! empty($webkitBrowsers)) {
            foreach ($webkitBrowsers as $browser) {
                $browserName = basename($browser);
                $this->line("✅ Found WebKit: {$browserName}");
            }
        }
    }

    private function checkNodeEnvironment(): void
    {
        $this->info("\n🟢 Checking Node.js Environment...");

        // Check Node.js version
        $nodeVersion = shell_exec('node --version 2>/dev/null');
        if (! $nodeVersion) {
            $this->error('❌ Node.js not found');

            return;
        }
        $this->line('✅ Node.js version: '.trim($nodeVersion));

        // Check npm version
        $npmVersion = shell_exec('npm --version 2>/dev/null');
        if (! $npmVersion) {
            $this->error('❌ npm not found');

            return;
        }
        $this->line('✅ npm version: '.trim($npmVersion));

        // Check if package.json exists and has playwright
        if (! file_exists(base_path('package.json'))) {
            $this->error('❌ package.json not found');

            return;
        }

        $packageJson = json_decode(file_get_contents(base_path('package.json')), true);
        if (isset($packageJson['dependencies']['playwright'])) {
            $version = $packageJson['dependencies']['playwright'];
            $this->line("✅ Playwright in dependencies: {$version}");
        } elseif (isset($packageJson['devDependencies']['playwright'])) {
            $version = $packageJson['devDependencies']['playwright'];
            $this->line("✅ Playwright in devDependencies: {$version}");
        } else {
            $this->error('❌ Playwright not found in package.json');
        }
    }

    private function checkBrowserDependencies(): void
    {
        $this->info("\n🔧 Checking Browser Dependencies...");

        // Check for common browser dependencies
        $dependencies = [
            'libnss3' => 'NSS library',
            'libgtk-3-0' => 'GTK library',
            'libgconf-2-4' => 'GConf library',
            'libxtst6' => 'X11 testing library',
            'libxss1' => 'X11 screensaver library',
            'libasound2' => 'ALSA sound library',
        ];

        foreach ($dependencies as $package => $description) {
            $result = shell_exec("dpkg -l | grep {$package} 2>/dev/null");
            if ($result) {
                $this->line("✅ {$description}: installed");
            } else {
                $this->warn("⚠️  {$description}: not found (may cause issues)");
            }
        }
    }

    private function checkPermissions(): void
    {
        $this->info("\n🔐 Checking Permissions...");

        $user = shell_exec('whoami 2>/dev/null');
        if ($user) {
            $this->line('✅ Running as user: '.trim($user));
        }

        $homeDir = $_SERVER['HOME'] ?? '/home/sail';
        if (is_writable($homeDir)) {
            $this->line("✅ Home directory writable: {$homeDir}");
        } else {
            $this->error("❌ Home directory not writable: {$homeDir}");
        }

        $tempDir = sys_get_temp_dir();
        if (is_writable($tempDir)) {
            $this->line("✅ Temp directory writable: {$tempDir}");
        } else {
            $this->error("❌ Temp directory not writable: {$tempDir}");
        }
    }
}
