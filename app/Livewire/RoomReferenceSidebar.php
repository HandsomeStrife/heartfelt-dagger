<?php

declare(strict_types=1);

namespace App\Livewire;

use Illuminate\Support\Facades\File;
use Livewire\Component;

class RoomReferenceSidebar extends Component
{
    public bool $is_sidebar = true;
    public string $current_page = 'what-is-this';
    public ?string $content_html = null;
    public string $page_title = 'What Is This?';

    /**
     * Define the static pages available in the reference section
     */
    protected array $staticPages = [
        // Introduction
        'what-is-this' => 'What Is This?',
        'the-basics' => 'The Basics',
        
        // Character Creation
        'character-creation' => 'Character Creation',
        
        // Core Materials
        'domains' => 'Domains',
        'classes' => 'Classes', 
        'ancestries' => 'Ancestries',
        'communities' => 'Communities',
        
        // Core Mechanics
        'flow-of-the-game' => 'Flow of the Game',
        'core-gameplay-loop' => 'Core Gameplay Loop',
        'the-spotlight' => 'The Spotlight',
        'turn-order-and-action-economy' => 'Turn Order & Action Economy',
        'making-moves-and-taking-action' => 'Making Moves & Taking Action',
        'combat' => 'Combat',
        'stress' => 'Stress',
        'attacking' => 'Attacking',
        'maps-range-and-movement' => 'Maps, Range, and Movement',
        'conditions' => 'Conditions',
        'downtime' => 'Downtime',
        'death' => 'Death',
        'additional-rules' => 'Additional Rules',
        'leveling-up' => 'Leveling Up',
        'multiclassing' => 'Multiclassing',
        
        // Equipment
        'equipment' => 'Equipment',
        'weapons' => 'Weapons',
        'combat-wheelchair' => 'Combat Wheelchair',
        'armor' => 'Armor',
        'loot' => 'Loot',
        'consumables' => 'Consumables',
        'gold' => 'Gold',
        
        // Running an Adventure
        'gm-guidance' => 'GM Guidance',
        'core-gm-mechanics' => 'Core GM Mechanics',
        'adversaries' => 'Adversaries',
        'environments' => 'Environments',
        'additional-gm-guidance' => 'Additional GM Guidance',
        'campaign-frames' => 'Campaign Frames',
        
        // Domain Abilities
        'arcana-abilities' => 'Arcana Abilities',
        'blade-abilities' => 'Blade Abilities',
        'bone-abilities' => 'Bone Abilities',
        'codex-abilities' => 'Codex Abilities',
        'grace-abilities' => 'Grace Abilities',
        'midnight-abilities' => 'Midnight Abilities',
        'sage-abilities' => 'Sage Abilities',
        'splendor-abilities' => 'Splendor Abilities',
        'valor-abilities' => 'Valor Abilities',
    ];

    /**
     * Mount component with initial page
     */
    public function mount(bool $isSidebar = true, string $initialPage = 'what-is-this'): void
    {
        $this->is_sidebar = $isSidebar;
        $this->current_page = $initialPage;
        $this->loadPage($initialPage);
    }

    /**
     * Load a specific reference page
     */
    public function loadPage(string $page): void
    {
        $this->current_page = $page;
        
        // Get page title
        $this->page_title = $this->staticPages[$page] ?? 'Reference';
        
        // Load the content
        $this->content_html = $this->renderPageContent($page);
    }

    /**
     * Render the page content as HTML
     */
    protected function renderPageContent(string $page): string
    {
        try {
            // Check for special pages (domains, classes, etc.)
            if ($this->isSpecialPage($page)) {
                return $this->renderSpecialPage($page);
            }
            
            // Check if static page view exists
            $viewPath = "reference.pages.{$page}";
            if (view()->exists($viewPath)) {
                return view($viewPath)->render();
            }
            
            return '<div class="text-slate-400 p-4">Page content not found</div>';
        } catch (\Exception $e) {
            return '<div class="text-red-400 p-4">Error loading page: ' . e($e->getMessage()) . '</div>';
        }
    }

    /**
     * Check if page is a special page with custom handling
     */
    protected function isSpecialPage(string $page): bool
    {
        $specialPages = [
            'domains',
            'arcana-abilities',
            'blade-abilities', 
            'bone-abilities',
            'codex-abilities',
            'grace-abilities',
            'midnight-abilities',
            'sage-abilities',
            'splendor-abilities',
            'valor-abilities',
            'classes',
            'ancestries',
            'communities',
        ];
        
        return in_array($page, $specialPages);
    }

    /**
     * Render special pages (domains, classes, etc.)
     */
    protected function renderSpecialPage(string $page): string
    {
        // Handle domain abilities pages
        if (str_ends_with($page, '-abilities')) {
            $domainKey = str_replace('-abilities', '', $page);
            $viewPath = "reference.special.domain-abilities";
            
            // Load domain abilities data
            $abilitiesData = $this->loadDomainAbilities($domainKey);
            
            if ($abilitiesData && view()->exists($viewPath)) {
                return view($viewPath, $abilitiesData)->render();
            }
        }
        
        // Handle special view pages
        $specialViewPath = "reference.special.{$page}";
        if (view()->exists($specialViewPath)) {
            return view($specialViewPath)->render();
        }
        
        return '<div class="text-slate-400 p-4">Special page content not available</div>';
    }

    /**
     * Load domain abilities data
     */
    protected function loadDomainAbilities(string $domainKey): ?array
    {
        try {
            $abilitiesPath = resource_path("json/abilities.json");
            
            if (!File::exists($abilitiesPath)) {
                return null;
            }
            
            $allAbilities = json_decode(File::get($abilitiesPath), true);
            
            // Filter abilities by domain
            $domainAbilities = collect($allAbilities)
                ->filter(fn ($ability) => ($ability['domain'] ?? null) === $domainKey)
                ->groupBy('level')
                ->sortKeys()
                ->toArray();
            
            // Load domain info
            $domainsPath = resource_path("json/domains.json");
            $domainInfo = null;
            
            if (File::exists($domainsPath)) {
                $domains = json_decode(File::get($domainsPath), true);
                $domainInfo = $domains[$domainKey] ?? null;
            }
            
            return [
                'abilities' => $domainAbilities,
                'domain_info' => $domainInfo,
                'domain_key' => $domainKey,
                'title' => ucfirst($domainKey) . ' Abilities',
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get all pages organized by section
     */
    public function getPagesProperty(): array
    {
        return [
            'Introduction' => [
                'what-is-this' => 'What Is This?',
                'the-basics' => 'The Basics',
            ],
            'Character Creation' => [
                'character-creation' => 'Character Creation',
            ],
            'Core Materials' => [
                'domains' => 'Domains',
                'arcana-abilities' => 'Arcana',
                'blade-abilities' => 'Blade',
                'bone-abilities' => 'Bone',
                'codex-abilities' => 'Codex',
                'grace-abilities' => 'Grace',
                'midnight-abilities' => 'Midnight',
                'sage-abilities' => 'Sage',
                'splendor-abilities' => 'Splendor',
                'valor-abilities' => 'Valor',
                'classes' => 'Classes', 
                'ancestries' => 'Ancestries',
                'communities' => 'Communities',
            ],
            'Core Mechanics' => [
                'flow-of-the-game' => 'Flow of the Game',
                'core-gameplay-loop' => 'Core Gameplay Loop',
                'the-spotlight' => 'The Spotlight',
                'turn-order-and-action-economy' => 'Turn Order & Action Economy',
                'making-moves-and-taking-action' => 'Making Moves & Taking Action',
                'combat' => 'Combat',
                'stress' => 'Stress',
                'attacking' => 'Attacking',
                'maps-range-and-movement' => 'Maps, Range, and Movement',
                'conditions' => 'Conditions',
                'downtime' => 'Downtime',
                'death' => 'Death',
                'additional-rules' => 'Additional Rules',
                'leveling-up' => 'Leveling Up',
                'multiclassing' => 'Multiclassing',
            ],
            'Equipment' => [
                'equipment' => 'Equipment',
                'weapons' => 'Weapons',
                'combat-wheelchair' => 'Combat Wheelchair',
                'armor' => 'Armor',
                'loot' => 'Loot',
                'consumables' => 'Consumables',
                'gold' => 'Gold',
            ],
            'Running an Adventure' => [
                'gm-guidance' => 'GM Guidance',
                'core-gm-mechanics' => 'Core GM Mechanics',
                'adversaries' => 'Adversaries',
                'environments' => 'Environments',
                'additional-gm-guidance' => 'Additional GM Guidance',
                'campaign-frames' => 'Campaign Frames',
            ],
        ];
    }

    public function render()
    {
        return view('livewire.room-reference-sidebar');
    }
}
