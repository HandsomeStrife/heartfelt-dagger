<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\Table\TableExtension;

class ConvertMarkdownToBladeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reference:convert-markdown
                           {--force : Overwrite existing Blade files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert markdown reference files to Blade files with TailwindCSS styling';

    private CommonMarkConverter $markdownConverter;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->initializeMarkdownConverter();

        $this->info('Converting markdown files to Blade files...');

        $directories = [
            'contents',
            'environments/Tier 1',
            'environments/Tier 2',
            'environments/Tier 3', 
            'environments/Tier 4',
            'frames'
        ];

        $totalFiles = 0;
        $convertedFiles = 0;

        foreach ($directories as $directory) {
            $sourceDir = resource_path("rules/{$directory}");
            
            // Convert directory names to use hyphens instead of spaces for Blade naming
            $targetDirectoryName = str_replace(' ', '-', strtolower($directory));
            $targetDir = resource_path("views/reference/pages/{$targetDirectoryName}");

            if (!File::isDirectory($sourceDir)) {
                $this->warn("Source directory not found: {$sourceDir}");
                continue;
            }

            // Create target directory if it doesn't exist
            if (!File::isDirectory($targetDir)) {
                File::makeDirectory($targetDir, 0755, true);
            }

            $markdownFiles = File::glob("{$sourceDir}/*.md");

            foreach ($markdownFiles as $markdownFile) {
                $totalFiles++;
                
                $fileName = pathinfo($markdownFile, PATHINFO_FILENAME);
                // Convert filename to use hyphens for consistency
                $bladeFileName = str_replace([' ', '\''], ['-', ''], $fileName);
                $bladeFile = "{$targetDir}/{$bladeFileName}.blade.php";

                if (File::exists($bladeFile) && !$this->option('force')) {
                    $this->warn("Skipping existing file: {$bladeFile} (use --force to overwrite)");
                    continue;
                }

                $this->info("Converting: {$fileName}");

                $markdownContent = File::get($markdownFile);
                $bladeContent = $this->convertMarkdownToBlade($markdownContent);

                File::put($bladeFile, $bladeContent);
                $convertedFiles++;
            }
        }

        $this->info("Conversion complete!");
        $this->info("Files processed: {$totalFiles}");
        $this->info("Files converted: {$convertedFiles}");

        return Command::SUCCESS;
    }

    private function initializeMarkdownConverter(): void
    {
        $environment = new Environment([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 10,
        ]);

        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());
        $environment->addExtension(new TableExtension());

        $this->markdownConverter = new CommonMarkConverter([], $environment);
    }

    private function convertMarkdownToBlade(string $markdownContent): string
    {
        // Convert markdown to HTML first
        $html = $this->markdownConverter->convert($markdownContent)->getContent();

        // Apply TailwindCSS classes to HTML elements
        $styledHtml = $this->applyTailwindStyles($html);

        return $styledHtml;
    }

    private function applyTailwindStyles(string $html): string
    {
        // Replace HTML elements with TailwindCSS styled versions
        $replacements = [
            // Headings
            '/<h1([^>]*)>(.*?)<\/h1>/s' => '<h1$1 class="font-outfit text-2xl font-bold text-white border-b border-slate-700 pb-3 mb-6 mt-0">$2</h1>',
            '/<h2([^>]*)>(.*?)<\/h2>/s' => '<h2$1 class="font-outfit text-xl font-bold text-amber-400 mt-8 mb-4">$2</h2>',
            '/<h3([^>]*)>(.*?)<\/h3>/s' => '<h3$1 class="font-outfit text-lg font-bold text-amber-300 mt-6 mb-3">$2</h3>',
            '/<h4([^>]*)>(.*?)<\/h4>/s' => '<h4$1 class="font-outfit text-base font-bold text-amber-200 mt-4 mb-2">$2</h4>',
            '/<h5([^>]*)>(.*?)<\/h5>/s' => '<h5$1 class="font-outfit text-sm font-bold text-amber-200 mt-3 mb-2">$2</h5>',
            '/<h6([^>]*)>(.*?)<\/h6>/s' => '<h6$1 class="font-outfit text-xs font-bold text-amber-200 mt-2 mb-1">$2</h6>',

            // Paragraphs
            '/<p([^>]*)>(.*?)<\/p>/s' => '<p$1 class="text-slate-300 leading-relaxed mb-4">$2</p>',

            // Lists
            '/<ul([^>]*)>/s' => '<ul$1 class="text-slate-300 list-disc list-inside mb-4 space-y-1">',
            '/<ol([^>]*)>/s' => '<ol$1 class="text-slate-300 list-decimal list-inside mb-4 space-y-1">',
            '/<li([^>]*)>(.*?)<\/li>/s' => '<li$1 class="leading-relaxed">$2</li>',

            // Text formatting
            '/<strong([^>]*)>(.*?)<\/strong>/s' => '<strong$1 class="text-white font-semibold">$2</strong>',
            '/<em([^>]*)>(.*?)<\/em>/s' => '<em$1 class="text-amber-300 not-italic">$2</em>',
            '/<code([^>]*)>(.*?)<\/code>/s' => '<code$1 class="text-amber-300 bg-slate-800 px-1 py-0.5 rounded text-sm">$2</code>',

            // Blockquotes
            '/<blockquote([^>]*)>(.*?)<\/blockquote>/s' => '<blockquote$1 class="border-l-4 border-amber-500 bg-amber-500/10 py-2 px-4 rounded-r text-amber-100 mb-4">$2</blockquote>',

            // Tables
            '/<table([^>]*)>/s' => '<table$1 class="border-collapse w-full mb-4">',
            '/<thead([^>]*)>/s' => '<thead$1>',
            '/<tbody([^>]*)>/s' => '<tbody$1>',
            '/<tr([^>]*)>/s' => '<tr$1>',
            '/<th([^>]*)>(.*?)<\/th>/s' => '<th$1 class="border border-slate-600 bg-slate-800 px-3 py-2 text-white font-semibold text-left">$2</th>',
            '/<td([^>]*)>(.*?)<\/td>/s' => '<td$1 class="border border-slate-600 px-3 py-2 text-slate-300">$2</td>',

            // Pre-formatted code blocks
            '/<pre([^>]*)>(.*?)<\/pre>/s' => '<pre$1 class="bg-slate-800 border border-slate-600 rounded p-4 mb-4 text-slate-300 text-sm overflow-x-auto">$2</pre>',

            // Links
            '/<a([^>]*href="[^"]*"[^>]*)>(.*?)<\/a>/s' => '<a$1 class="text-amber-400 hover:text-amber-300 underline">$2</a>',

            // Horizontal rules
            '/<hr([^>]*)>/s' => '<hr$1 class="border-slate-700 my-8">',
        ];

        foreach ($replacements as $pattern => $replacement) {
            $html = preg_replace($pattern, $replacement, $html);
        }

        return $html;
    }
}
