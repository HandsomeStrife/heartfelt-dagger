<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class ReferenceController extends Controller
{
    protected array $referenceData = [];

    public function __construct()
    {
        $this->loadReferenceData();
    }

    public function index(): View
    {
        // Redirect to show the "What Is This" page by default
        return $this->show('what-is-this');
    }

    public function show(string $page): View
    {
        // Check if this is a special page that needs custom handling
        if ($this->isSpecialPage($page)) {
            return $this->showSpecialPage($page);
        }

        $pageData = $this->referenceData['content'][$page] ?? null;

        if (!$pageData) {
            abort(404, 'Reference page not found');
        }

        // Process the content for better rendering
        $processedSections = [];
        foreach ($pageData['sections'] as $sectionTitle => $section) {
            $processedSections[] = [
                'title' => $sectionTitle === '_intro' ? null : $sectionTitle,
                'content' => $this->processContent($section['content']),
                'anchor' => $section['anchor'],
                'word_count' => $section['word_count']
            ];
        }

        $pageData['processed_sections'] = $processedSections;

        // Pass all pages for navigation
        $pages = $this->referenceData['content'] ?? [];

        return view('reference.page', compact('pageData', 'page', 'pages'));
    }

    protected function isSpecialPage(string $page): bool
    {
        $specialPages = [
            'domains',
            'classes',
            'ancestries',
            'communities',
            // Domain abilities pages
            'arcana-abilities',
            'blade-abilities', 
            'bone-abilities',
            'codex-abilities',
            'grace-abilities',
            'midnight-abilities',
            'sage-abilities',
            'splendor-abilities',
            'valor-abilities',
        ];

        // Check for individual class pages
        $classes = ['bard', 'druid', 'guardian', 'ranger', 'rogue', 'seraph', 'sorcerer', 'warrior', 'wizard'];
        if (in_array($page, $classes)) {
            return true;
        }

        return in_array($page, $specialPages);
    }

    protected function showSpecialPage(string $page): View
    {
        $pages = $this->referenceData['content'] ?? [];

        switch ($page) {
            case 'domains':
                return $this->showDomainsPage($pages);
            
            case 'classes':
                return $this->showClassesPage($pages);
            
            case 'ancestries':
                return $this->showAncestriesPage($pages);
            
            case 'communities':
                return $this->showCommunitiesPage($pages);
            
            default:
                // Check if it's a domain abilities page
                if (str_ends_with($page, '-abilities')) {
                    $domain = str_replace('-abilities', '', $page);
                    return $this->showDomainAbilitiesPage($domain, $pages);
                }
                
                // Check if it's an individual class page
                $classes = ['bard', 'druid', 'guardian', 'ranger', 'rogue', 'seraph', 'sorcerer', 'warrior', 'wizard'];
                if (in_array($page, $classes)) {
                    return $this->showClassDetailPage($page, $pages);
                }
                
                abort(404, 'Special page handler not found');
        }
    }

    protected function showDomainsPage(array $pages): View
    {
        $domains = $this->loadGameData('domains');
        
        return view('reference.special.domains', [
            'domains' => $domains,
            'pages' => $pages,
            'page' => 'domains',
            'title' => 'Domains'
        ]);
    }

    protected function showClassesPage(array $pages): View
    {
        $classes = $this->loadGameData('classes');
        
        return view('reference.special.classes', [
            'classes' => $classes,
            'pages' => $pages,
            'page' => 'classes',
            'title' => 'Classes'
        ]);
    }

    protected function showAncestriesPage(array $pages): View
    {
        $ancestries = $this->loadGameData('ancestries');
        
        return view('reference.special.ancestries', [
            'ancestries' => $ancestries,
            'pages' => $pages,
            'page' => 'ancestries',
            'title' => 'Ancestries'
        ]);
    }

    protected function showCommunitiesPage(array $pages): View
    {
        $communities = $this->loadGameData('communities');
        
        return view('reference.special.communities', [
            'communities' => $communities,
            'pages' => $pages,
            'page' => 'communities',
            'title' => 'Communities'
        ]);
    }

    protected function showDomainAbilitiesPage(string $domain, array $pages): View
    {
        $domains = $this->loadGameData('domains');
        $abilities = $this->loadGameData('abilities');
        
        // Get domain info
        $domainInfo = $domains[$domain] ?? null;
        if (!$domainInfo) {
            abort(404, 'Domain not found');
        }

        // Filter abilities for this domain
        $domainAbilities = [];
        foreach ($abilities as $abilityKey => $ability) {
            if (($ability['domain'] ?? '') === $domain) {
                $domainAbilities[$abilityKey] = $ability;
            }
        }

        // Group abilities by level
        $abilitiesByLevel = [];
        foreach ($domainAbilities as $key => $ability) {
            $level = $ability['level'] ?? 1;
            if (!isset($abilitiesByLevel[$level])) {
                $abilitiesByLevel[$level] = [];
            }
            $abilitiesByLevel[$level][$key] = $ability;
        }
        
        // Sort levels numerically (ascending order)
        ksort($abilitiesByLevel, SORT_NUMERIC);

        return view('reference.special.domain-abilities', [
            'domain_key' => $domain,
            'domain_info' => $domainInfo,
            'abilities' => $domainAbilities,
            'abilities_by_level' => $abilitiesByLevel,
            'pages' => $pages,
            'page' => $domain . '-abilities',
            'title' => ucfirst($domain) . ' Abilities'
        ]);
    }

    protected function showClassDetailPage(string $classKey, array $pages): View
    {
        $classes = $this->loadGameData('classes');
        $subclasses = $this->loadGameData('subclasses');
        
        // Get class info
        $classInfo = $classes[$classKey] ?? null;
        if (!$classInfo) {
            abort(404, 'Class not found');
        }

        // Get subclasses for this class
        $classSubclasses = [];
        foreach ($subclasses as $subclassKey => $subclass) {
            if (($subclass['class'] ?? '') === $classKey) {
                $classSubclasses[$subclassKey] = $subclass;
            }
        }

        return view('reference.special.class-detail', [
            'class_key' => $classKey,
            'class_info' => $classInfo,
            'class_subclasses' => $classSubclasses,
            'pages' => $pages,
            'page' => $classKey,
            'title' => $classInfo['name'] ?? ucfirst($classKey)
        ]);
    }

    protected function loadGameData(string $type): array
    {
        $cacheKey = "game-data-{$type}";
        
        return Cache::remember($cacheKey, 3600, function() use ($type) {
            $jsonFile = resource_path("json/{$type}.json");
            
            if (!File::exists($jsonFile)) {
                return [];
            }

            return json_decode(File::get($jsonFile), true);
        });
    }

    protected function loadReferenceData(): void
    {
        $cacheKey = 'reference-data-v3';
        
        $this->referenceData = Cache::remember($cacheKey, 3600, function () {
            $jsonFile = resource_path('json/reference-content.json');
            
            if (!File::exists($jsonFile)) {
                return ['categories' => [], 'content' => []];
            }

            return json_decode(File::get($jsonFile), true);
        });
    }

    protected function processContent(string $content): string
    {
        // Convert markdown-style content to HTML
        $html = $content;

        // Process headers
        $html = preg_replace('/^### (.+)$/m', '<h3 class="font-outfit text-lg font-bold text-amber-300 mt-6 mb-3">$1</h3>', $html);
        $html = preg_replace('/^## (.+)$/m', '<h2 class="font-outfit text-xl font-bold text-amber-400 mt-8 mb-4">$1</h2>', $html);
        $html = preg_replace('/^# (.+)$/m', '<h1 class="font-outfit text-2xl font-bold text-white border-b border-slate-700 pb-3 mb-6 mt-0">$1</h1>', $html);

        // Process paragraphs (simple approach - split by double newlines)
        $paragraphs = explode("\n\n", $html);
        $processedParagraphs = [];

        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if (empty($paragraph)) continue;

            // Skip if already contains HTML tags (headers, etc.)
            if (preg_match('/<[^>]+>/', $paragraph)) {
                $processedParagraphs[] = $paragraph;
                continue;
            }

            // Process tables
            if (str_contains($paragraph, '|')) {
                $processedParagraphs[] = $this->processTable($paragraph);
                continue;
            }

            // Process lists
            if (preg_match('/^[\s]*[-\*\+]/', $paragraph)) {
                $processedParagraphs[] = $this->processList($paragraph);
                continue;
            }

            // Process blockquotes
            if (str_starts_with($paragraph, '>')) {
                $processedParagraphs[] = $this->processBlockquote($paragraph);
                continue;
            }

            // Regular paragraph
            $paragraph = $this->processInlineFormatting($paragraph);
            $processedParagraphs[] = '<p class="text-slate-300 leading-relaxed mb-4">' . $paragraph . '</p>';
        }

        return implode("\n", $processedParagraphs);
    }

    protected function processTable(string $content): string
    {
        $lines = explode("\n", $content);
        $tableLines = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (str_contains($line, '|')) {
                $tableLines[] = $line;
            }
        }

        if (count($tableLines) < 2) {
            return $content; // Not a valid table
        }

        $html = '<div class="overflow-x-auto mb-6">';
        $html .= '<table class="w-full border-collapse border border-slate-600">';

        foreach ($tableLines as $index => $line) {
            // Skip separator lines (like |---|---|)
            if (preg_match('/^\|[\s\-\|:]+\|?$/', $line)) {
                continue;
            }

            $cells = array_map('trim', explode('|', trim($line, '|')));
            $isHeader = $index === 0;
            $tag = $isHeader ? 'th' : 'td';
            $class = $isHeader 
                ? 'border border-slate-600 bg-slate-800 px-4 py-3 text-left font-semibold text-amber-400'
                : 'border border-slate-600 px-4 py-3 text-slate-300';

            $html .= '<tr>';
            foreach ($cells as $cell) {
                $cell = $this->processInlineFormatting($cell);
                $html .= "<{$tag} class=\"{$class}\">{$cell}</{$tag}>";
            }
            $html .= '</tr>';
        }

        $html .= '</table></div>';
        return $html;
    }

    protected function processList(string $content): string
    {
        $lines = explode("\n", $content);
        $html = '<ul class="text-slate-300 list-disc list-inside mb-4 space-y-1">';

        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^[\s]*[-\*\+]\s*(.+)$/', $line, $matches)) {
                $text = $this->processInlineFormatting($matches[1]);
                $html .= '<li class="leading-relaxed">' . $text . '</li>';
            }
        }

        $html .= '</ul>';
        return $html;
    }

    protected function processBlockquote(string $content): string
    {
        $lines = explode("\n", $content);
        $quoteContent = [];

        foreach ($lines as $line) {
            if (str_starts_with($line, '>')) {
                $quoteContent[] = trim(substr($line, 1));
            }
        }

        $text = $this->processInlineFormatting(implode(' ', $quoteContent));
        
        return '<blockquote class="border-l-4 border-amber-500 bg-amber-500/10 py-2 px-4 rounded-r text-amber-100 mb-4">' .
               '<p class="text-slate-300 leading-relaxed mb-4">' . $text . '</p>' .
               '</blockquote>';
    }

    protected function processInlineFormatting(string $text): string
    {
        // Bold
        $text = preg_replace('/\*\*(.+?)\*\*/', '<strong class="text-white font-semibold">$1</strong>', $text);
        
        // Italic/Emphasis
        $text = preg_replace('/\*([^*]+)\*/', '<em class="text-amber-300 not-italic">$1</em>', $text);
        
        // Code (backticks)
        $text = preg_replace('/`([^`]+)`/', '<code class="bg-slate-800 text-amber-300 px-1 rounded">$1</code>', $text);

        return $text;
    }
}