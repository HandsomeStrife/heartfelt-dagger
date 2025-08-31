<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixBrowserTests extends Command
{
    protected $signature = 'browser:fix-waitfortext';

    protected $description = 'Fix invalid waitForText() calls in browser tests with proper Pest 4 methods';

    public function handle(): int
    {
        $this->info('🔍 Scanning for invalid waitForText() calls in browser tests...');
        
        $browserTestsDir = base_path('tests/Browser');
        $files = File::allFiles($browserTestsDir);
        
        $fixedFiles = 0;
        $totalIssues = 0;
        
        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            
            $filePath = $file->getRealPath();
            $content = File::get($filePath);
            $originalContent = $content;
            
            // Count waitForText instances in this file
            $matches = [];
            preg_match_all('/->waitForText\s*\(/', $content, $matches);
            $issuesInFile = count($matches[0]);
            
            if ($issuesInFile > 0) {
                $this->warn("📁 {$file->getRelativePathname()}: {$issuesInFile} waitForText() calls found");
                $totalIssues += $issuesInFile;
                
                // Replace waitForText patterns with proper alternatives
                
                // Pattern 1: ->waitForText('text', timeout) followed by ->assertSee('text')
                $content = preg_replace(
                    '/->waitForText\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*\d+\s*\)\s*->assertSee\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/m',
                    '->wait(2) // Wait for content to load' . "\n" . '            ->assertSee(\'$1\')',
                    $content
                );
                
                // Pattern 2: ->waitForText('text', timeout) standalone
                $content = preg_replace(
                    '/->waitForText\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*\d+\s*\)/m',
                    '->wait(2) // Wait for content to load' . "\n" . '            ->assertSee(\'$1\')',
                    $content
                );
                
                // Pattern 3: ->waitForText('text') without timeout
                $content = preg_replace(
                    '/->waitForText\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/m',
                    '->wait(2) // Wait for content to load' . "\n" . '            ->assertSee(\'$1\')',
                    $content
                );
                
                if ($content !== $originalContent) {
                    File::put($filePath, $content);
                    $fixedFiles++;
                    $this->info("✅ Fixed {$file->getRelativePathname()}");
                }
            }
        }
        
        $this->info("\n📊 Summary:");
        $this->info("Total invalid waitForText() calls found: {$totalIssues}");
        $this->info("Files fixed: {$fixedFiles}");
        
        if ($totalIssues > 0) {
            $this->warn("\n⚠️  Manual Review Required:");
            $this->warn("The automated fixes use generic 2-second waits.");
            $this->warn("You may need to adjust wait times based on your specific use cases.");
            $this->warn("Test each fixed file to ensure the timing is appropriate.");
        }
        
        return 0;
    }
}
