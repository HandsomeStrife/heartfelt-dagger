<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class ReferenceController extends Controller
{
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
    ];

    /**
     * Special pages that have custom handling (like domains with existing styling)
     */
    protected array $specialPages = [
        'domains',
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

        // Check if the static page exists
        if (!$this->pageExists($page)) {
            abort(404, 'Reference page not found');
        }

        // For static pages, pass the current page context
        return view("reference.pages.{$page}", [
            'current_page' => $page
        ]);
    }

    protected function isSpecialPage(string $page): bool
    {
        return in_array($page, $this->specialPages);
    }

    protected function pageExists(string $page): bool
    {
        // Check if it's in our defined static pages
        if (array_key_exists($page, $this->staticPages)) {
            // Check if the Blade file actually exists
            $viewPath = resource_path("views/reference/pages/{$page}.blade.php");
            return File::exists($viewPath);
        }

        return false;
    }

    protected function showSpecialPage(string $page): View
    {
        switch ($page) {
            case 'domains':
                return $this->showDomainsPage();
            
            // Domain abilities pages
            case 'arcana-abilities':
            case 'blade-abilities':
            case 'bone-abilities':
            case 'codex-abilities':
            case 'grace-abilities':
            case 'midnight-abilities':
            case 'sage-abilities':
            case 'splendor-abilities':
            case 'valor-abilities':
                return $this->showDomainAbilitiesPage($page);
            
            default:
                abort(404, 'Special page not found');
        }
    }

    protected function showDomainsPage(): View
    {
        // Load domains data from JSON
        $domainsFile = resource_path('json/domains.json');
        $domains = [];
        
        if (File::exists($domainsFile)) {
            $domains = json_decode(File::get($domainsFile), true);
        }

        $title = 'Domains';
        $current_page = 'domains';

        return view('reference.special.domains', compact('domains', 'title', 'current_page'));
    }

    protected function showDomainAbilitiesPage(string $page): View
    {
        // Extract domain key from page name (e.g., 'arcana-abilities' -> 'arcana')
        $domainKey = str_replace('-abilities', '', $page);
        
        // Load abilities data from JSON
        $abilitiesFile = resource_path('json/abilities.json');
        $abilities = [];
        $domainInfo = null;
        
        if (File::exists($abilitiesFile)) {
            $allAbilities = json_decode(File::get($abilitiesFile), true);
            
            // Filter abilities for this domain
            $abilities = array_filter($allAbilities, function($ability) use ($domainKey) {
                return isset($ability['domain']) && $ability['domain'] === $domainKey;
            });
        }

        // Load domain info
        $domainsFile = resource_path('json/domains.json');
        if (File::exists($domainsFile)) {
            $domains = json_decode(File::get($domainsFile), true);
            $domainInfo = $domains[$domainKey] ?? null;
        }

        // Group abilities by level
        $abilitiesByLevel = [];
        foreach ($abilities as $key => $ability) {
            $level = $ability['level'] ?? 1;
            $abilitiesByLevel[$level][$key] = $ability;
        }

        // Sort levels
        ksort($abilitiesByLevel);

        $title = ucfirst($domainKey) . ' Abilities';
        $domain_key = $domainKey;
        $abilities_by_level = $abilitiesByLevel;
        $domain_info = $domainInfo;
        $current_page = $page;

        return view('reference.special.domain-abilities', compact(
            'title', 
            'domain_key', 
            'abilities_by_level', 
            'domain_info',
            'current_page'
        ));
    }

    /**
     * Get all available pages for navigation
     */
    public function getAvailablePages(): array
    {
        return $this->staticPages;
    }
}