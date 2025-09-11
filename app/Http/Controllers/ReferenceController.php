<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class ReferenceController extends Controller
{
    private array $pages = [
        // Introduction
        'what-is-this' => ['title' => 'What Is This', 'type' => 'blade', 'path' => 'contents/What Is This.md'],
        'the-basics' => ['title' => 'The Basics', 'type' => 'blade', 'path' => 'contents/The Basics.md'],
        
        // Character Creation
        'character-creation' => ['title' => 'Character Creation', 'type' => 'blade', 'path' => 'contents/Character Creation.md'],
        
        // Core Materials - these will use JSON data
        'domains' => ['title' => 'Domains', 'type' => 'json', 'data_source' => 'domains'],
        'classes' => ['title' => 'Classes', 'type' => 'json', 'data_source' => 'classes'],
        'ancestries' => ['title' => 'Ancestries', 'type' => 'json', 'data_source' => 'ancestries'],
        'communities' => ['title' => 'Communities', 'type' => 'json', 'data_source' => 'communities'],
        
        // Core Mechanics
        'flow-of-the-game' => ['title' => 'Flow of the Game', 'type' => 'blade', 'path' => 'contents/Flow of the Game.md'],
        'core-gameplay-loop' => ['title' => 'Core Gameplay Loop', 'type' => 'blade', 'path' => 'contents/Core Gameplay Loop.md'],
        'the-spotlight' => ['title' => 'The Spotlight', 'type' => 'blade', 'path' => 'contents/The Spotlight.md'],
        'turn-order-and-action-economy' => ['title' => 'Turn Order and Action Economy', 'type' => 'blade', 'path' => 'contents/Turn Order and Action Economy.md'],
        'making-moves-and-taking-action' => ['title' => 'Making Moves and Taking Action', 'type' => 'blade', 'path' => 'contents/Making Moves and Taking Action.md'],
        'combat' => ['title' => 'Combat', 'type' => 'blade', 'path' => 'contents/Combat.md'],
        'stress' => ['title' => 'Stress', 'type' => 'blade', 'path' => 'contents/Stress.md'],
        'attacking' => ['title' => 'Attacking', 'type' => 'blade', 'path' => 'contents/Attacking.md'],
        'maps-range-and-movement' => ['title' => 'Maps, Range, and Movement', 'type' => 'blade', 'path' => 'contents/Maps, Range, and Movement.md'],
        'conditions' => ['title' => 'Conditions', 'type' => 'blade', 'path' => 'contents/Conditions.md'],
        'downtime' => ['title' => 'Downtime', 'type' => 'blade', 'path' => 'contents/Downtime.md'],
        'death' => ['title' => 'Death', 'type' => 'blade', 'path' => 'contents/Death.md'],
        'additional-rules' => ['title' => 'Additional Rules', 'type' => 'blade', 'path' => 'contents/Additional Rules.md'],
        'leveling-up' => ['title' => 'Leveling Up', 'type' => 'blade', 'path' => 'contents/Leveling Up.md'],
        'multiclassing' => ['title' => 'Multiclassing', 'type' => 'blade', 'path' => 'contents/Multiclassing.md'],
        
        // Equipment - these will use JSON data
        'equipment' => ['title' => 'Equipment', 'type' => 'blade', 'path' => 'contents/Equipment.md'],
        'weapons' => ['title' => 'Weapons', 'type' => 'json', 'data_source' => 'weapons'],
        'armor' => ['title' => 'Armor', 'type' => 'json', 'data_source' => 'armor'],
        'consumables' => ['title' => 'Consumables', 'type' => 'json', 'data_source' => 'consumables'],
        'items' => ['title' => 'Items', 'type' => 'json', 'data_source' => 'items'],
        'primary-weapon-tables' => ['title' => 'Primary Weapon Tables', 'type' => 'blade', 'path' => 'contents/Primary Weapon Tables.md'],
        'secondary-weapon-tables' => ['title' => 'Secondary Weapon Tables', 'type' => 'blade', 'path' => 'contents/Secondary Weapon Tables.md'],
        'combat-wheelchair' => ['title' => 'Combat Wheelchair', 'type' => 'blade', 'path' => 'contents/Combat Wheelchair.md'],
        'armor-tables' => ['title' => 'Armor Tables', 'type' => 'blade', 'path' => 'contents/Armor Tables.md'],
        'loot' => ['title' => 'Loot', 'type' => 'blade', 'path' => 'contents/Loot.md'],
        'gold' => ['title' => 'Gold', 'type' => 'blade', 'path' => 'contents/Gold.md'],
        
        // GM Resources
        'gm-guidance' => ['title' => 'GM Guidance', 'type' => 'blade', 'path' => 'contents/GM Guidance.md'],
        'core-gm-mechanics' => ['title' => 'Core GM Mechanics', 'type' => 'blade', 'path' => 'contents/Core GM Mechanics.md'],
        'adversaries' => ['title' => 'Adversaries', 'type' => 'json', 'data_source' => 'adversaries'],
        'additional-gm-guidance' => ['title' => 'Additional GM Guidance', 'type' => 'blade', 'path' => 'contents/Additional GM Guidance.md'],
        
        // Reference
        'domain-abilities' => ['title' => 'Domain Abilities', 'type' => 'json', 'data_source' => 'abilities'],
        'domain-card-reference' => ['title' => 'Domain Card Reference', 'type' => 'blade', 'path' => 'contents/Domain Card Reference.md'],
        
        // Individual Domain Abilities Pages
        'arcana-abilities' => ['title' => 'Arcana Domain Abilities', 'type' => 'domain-abilities', 'domain' => 'arcana'],
        'blade-abilities' => ['title' => 'Blade Domain Abilities', 'type' => 'domain-abilities', 'domain' => 'blade'],
        'bone-abilities' => ['title' => 'Bone Domain Abilities', 'type' => 'domain-abilities', 'domain' => 'bone'],
        'codex-abilities' => ['title' => 'Codex Domain Abilities', 'type' => 'domain-abilities', 'domain' => 'codex'],
        'grace-abilities' => ['title' => 'Grace Domain Abilities', 'type' => 'domain-abilities', 'domain' => 'grace'],
        'midnight-abilities' => ['title' => 'Midnight Domain Abilities', 'type' => 'domain-abilities', 'domain' => 'midnight'],
        'sage-abilities' => ['title' => 'Sage Domain Abilities', 'type' => 'domain-abilities', 'domain' => 'sage'],
        'splendor-abilities' => ['title' => 'Splendor Domain Abilities', 'type' => 'domain-abilities', 'domain' => 'splendor'],
        'valor-abilities' => ['title' => 'Valor Domain Abilities', 'type' => 'domain-abilities', 'domain' => 'valor'],
    ];

    public function index(): View
    {
        // Default to first page if no specific page requested
        $defaultPage = 'what-is-this';
        return $this->show($defaultPage);
    }

    public function show(string $page = 'what-is-this'): View
    {
        if (!isset($this->pages[$page])) {
            abort(404);
        }

        $pageData = $this->pages[$page];
        
        if ($pageData['type'] === 'json') {
            // Handle JSON data pages
            $jsonData = $this->loadJsonData($pageData['data_source']);
            
            return view('reference.index', [
                'title' => $pageData['title'],
                'content_type' => 'json',
                'json_data' => $jsonData,
                'data_source' => $pageData['data_source'],
                'current_page' => $page,
                'pages' => $this->getPageTitles()
            ]);
        } elseif ($pageData['type'] === 'domain-abilities') {
            // Handle domain abilities pages
            $abilitiesData = $this->loadJsonData('abilities');
            $domainsData = $this->loadJsonData('domains');
            $domainKey = $pageData['domain'];
            
            // Filter abilities for this domain
            $domainAbilities = array_filter($abilitiesData, function($ability) use ($domainKey) {
                return isset($ability['domain']) && $ability['domain'] === $domainKey;
            });
            
            // Get domain info
            $domainInfo = $domainsData[$domainKey] ?? null;
            
            return view('reference.index', [
                'title' => $pageData['title'],
                'content_type' => 'domain-abilities',
                'abilities' => $domainAbilities,
                'domain_info' => $domainInfo,
                'domain_key' => $domainKey,
                'current_page' => $page,
                'pages' => $this->getPageTitles()
            ]);
        } else {
            // Handle Blade file pages
            $bladeViewPath = $this->getBladeViewPath($pageData['path']);
            
            // Check if the Blade file exists
            if (!view()->exists($bladeViewPath)) {
                abort(404, 'Reference page not found');
            }

            return view('reference.index', [
                'title' => $pageData['title'],
                'content_type' => 'blade',
                'content_view' => $bladeViewPath,
                'current_page' => $page,
                'pages' => $this->getPageTitles()
            ]);
        }
    }

    private function getBladeViewPath(string $filePath): string
    {
        // Convert file path to Blade view path
        // Example: "environments/Tier 1/Abandoned Grove.md" -> "reference.pages.environments.tier-1.abandoned-grove"
        $pathParts = explode('/', $filePath);
        $fileName = pathinfo(end($pathParts), PATHINFO_FILENAME);
        
        // Remove the .md extension from path parts
        array_pop($pathParts);
        
        // Build the view path
        $viewPath = 'reference.pages';
        foreach ($pathParts as $part) {
            // Convert directory names to lowercase and replace spaces with dashes for Laravel view naming
            $viewPath .= '.' . str_replace(' ', '-', strtolower($part));
        }
        
        // Convert filename to match the conversion command output (hyphens instead of spaces/apostrophes)
        $bladeFileName = str_replace([' ', '\''], ['-', ''], strtolower($fileName));
        $viewPath .= '.' . $bladeFileName;
        
        return $viewPath;
    }

    private function loadJsonData(string $source): array
    {
        $filePath = resource_path("json/{$source}.json");
        
        if (!File::exists($filePath)) {
            abort(404, "JSON data source not found: {$source}");
        }
        
        $jsonContent = File::get($filePath);
        $data = json_decode($jsonContent, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            abort(500, "Invalid JSON data in {$source}.json");
        }
        
        return $data;
    }

    private function getPageTitles(): array
    {
        return array_map(fn($page) => $page['title'], $this->pages);
    }
}