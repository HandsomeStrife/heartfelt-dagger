<?php

declare(strict_types=1);

namespace Domain\Character\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * Service for loading and caching DaggerHeart game data from JSON files.
 *
 * This service provides a single source of truth for loading game data,
 * with built-in caching, error handling, and logging.
 *
 * Benefits:
 * - Centralized JSON loading logic (DRY principle)
 * - Performance optimization via Laravel cache
 * - Consistent error handling across the application
 * - Easy cache invalidation for data updates
 * - Type-safe methods for each data type
 *
 * @see resources/json/ for game data files
 */
class GameDataLoader
{
    /**
     * Cache TTL in seconds (1 hour by default)
     */
    private const CACHE_TTL = 3600;

    /**
     * Cache key prefix for namespacing
     */
    private const CACHE_PREFIX = 'game_data.';

    /**
     * Load class data for a specific class key.
     *
     * @param string $classKey The class key (e.g., 'warrior', 'wizard')
     * @return array The class data array, or empty array if not found
     */
    public function loadClassData(string $classKey): array
    {
        $allClasses = $this->loadClasses();

        if (! isset($allClasses[$classKey])) {
            Log::warning('Class data not found', [
                'class_key' => $classKey,
                'available_classes' => array_keys($allClasses),
            ]);

            return [];
        }

        return $allClasses[$classKey];
    }

    /**
     * Load all classes data.
     *
     * @return array All classes indexed by class key
     */
    public function loadClasses(): array
    {
        return $this->loadJsonFile('classes.json', 'classes');
    }

    /**
     * Load subclass data for a specific subclass key.
     *
     * @param string $subclassKey The subclass key
     * @return array The subclass data array, or empty array if not found
     */
    public function loadSubclassData(string $subclassKey): array
    {
        $allSubclasses = $this->loadSubclasses();

        if (! isset($allSubclasses[$subclassKey])) {
            Log::warning('Subclass data not found', [
                'subclass_key' => $subclassKey,
                'available_subclasses' => array_keys($allSubclasses),
            ]);

            return [];
        }

        return $allSubclasses[$subclassKey];
    }

    /**
     * Load all subclasses data.
     *
     * @return array All subclasses indexed by subclass key
     */
    public function loadSubclasses(): array
    {
        return $this->loadJsonFile('subclasses.json', 'subclasses');
    }

    /**
     * Load ancestry data for a specific ancestry key.
     *
     * @param string $ancestryKey The ancestry key
     * @return array The ancestry data array, or empty array if not found
     */
    public function loadAncestryData(string $ancestryKey): array
    {
        $allAncestries = $this->loadAncestries();

        if (! isset($allAncestries[$ancestryKey])) {
            Log::warning('Ancestry data not found', [
                'ancestry_key' => $ancestryKey,
                'available_ancestries' => array_keys($allAncestries),
            ]);

            return [];
        }

        return $allAncestries[$ancestryKey];
    }

    /**
     * Load all ancestries data.
     *
     * @return array All ancestries indexed by ancestry key
     */
    public function loadAncestries(): array
    {
        return $this->loadJsonFile('ancestries.json', 'ancestries');
    }

    /**
     * Load community data for a specific community key.
     *
     * @param string $communityKey The community key
     * @return array The community data array, or empty array if not found
     */
    public function loadCommunityData(string $communityKey): array
    {
        $allCommunities = $this->loadCommunities();

        if (! isset($allCommunities[$communityKey])) {
            Log::warning('Community data not found', [
                'community_key' => $communityKey,
                'available_communities' => array_keys($allCommunities),
            ]);

            return [];
        }

        return $allCommunities[$communityKey];
    }

    /**
     * Load all communities data.
     *
     * @return array All communities indexed by community key
     */
    public function loadCommunities(): array
    {
        return $this->loadJsonFile('communities.json', 'communities');
    }

    /**
     * Load domain data for a specific domain key.
     *
     * @param string $domainKey The domain key (e.g., 'blade', 'arcana')
     * @return array The domain data array, or empty array if not found
     */
    public function loadDomainData(string $domainKey): array
    {
        $allDomains = $this->loadDomains();

        if (! isset($allDomains[$domainKey])) {
            Log::warning('Domain data not found', [
                'domain_key' => $domainKey,
                'available_domains' => array_keys($allDomains),
            ]);

            return [];
        }

        return $allDomains[$domainKey];
    }

    /**
     * Load all domains data.
     *
     * @return array All domains indexed by domain key
     */
    public function loadDomains(): array
    {
        return $this->loadJsonFile('domains.json', 'domains');
    }

    /**
     * Load all abilities (domain cards) data.
     *
     * @return array All abilities indexed by ability key
     */
    public function loadAbilities(): array
    {
        return $this->loadJsonFile('abilities.json', 'abilities');
    }

    /**
     * Load ability data for a specific ability key.
     *
     * @param string $abilityKey The ability key
     * @return array The ability data array, or empty array if not found
     */
    public function loadAbilityData(string $abilityKey): array
    {
        $allAbilities = $this->loadAbilities();

        if (! isset($allAbilities[$abilityKey])) {
            Log::warning('Ability data not found', [
                'ability_key' => $abilityKey,
                'available_abilities' => count($allAbilities) > 100 ? count($allAbilities).' abilities' : array_keys($allAbilities),
            ]);

            return [];
        }

        return $allAbilities[$abilityKey];
    }

    /**
     * Load all weapons data.
     *
     * @return array All weapons indexed by weapon key
     */
    public function loadWeapons(): array
    {
        return $this->loadJsonFile('weapons.json', 'weapons');
    }

    /**
     * Load all armor data.
     *
     * @return array All armor indexed by armor key
     */
    public function loadArmor(): array
    {
        return $this->loadJsonFile('armor.json', 'armor');
    }

    /**
     * Load all items data.
     *
     * @return array All items indexed by item key
     */
    public function loadItems(): array
    {
        return $this->loadJsonFile('items.json', 'items');
    }

    /**
     * Load all consumables data.
     *
     * @return array All consumables indexed by consumable key
     */
    public function loadConsumables(): array
    {
        return $this->loadJsonFile('consumables.json', 'consumables');
    }

    /**
     * Clear all cached game data.
     *
     * Useful when game data files are updated.
     *
     * @return void
     */
    public function clearCache(): void
    {
        $files = [
            'classes.json',
            'subclasses.json',
            'ancestries.json',
            'communities.json',
            'domains.json',
            'abilities.json',
            'weapons.json',
            'armor.json',
            'items.json',
            'consumables.json',
        ];

        foreach ($files as $file) {
            $cacheKey = $this->getCacheKey($file);
            Cache::forget($cacheKey);
        }

        Log::info('Game data cache cleared');
    }

    /**
     * Clear cache for a specific data file.
     *
     * @param string $filename The JSON filename (e.g., 'classes.json')
     * @return void
     */
    public function clearFileCache(string $filename): void
    {
        $cacheKey = $this->getCacheKey($filename);
        Cache::forget($cacheKey);

        Log::info('Game data file cache cleared', ['file' => $filename]);
    }

    /**
     * Load a JSON file with caching and error handling.
     *
     * @param string $filename The JSON filename (e.g., 'classes.json')
     * @param string $dataType Human-readable data type for logging (e.g., 'classes')
     * @return array The parsed JSON data, or empty array on error
     */
    private function loadJsonFile(string $filename, string $dataType): array
    {
        $cacheKey = $this->getCacheKey($filename);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filename, $dataType) {
            $path = resource_path("json/{$filename}");

            // Check if file exists
            if (! File::exists($path)) {
                Log::error('Game data file not found', [
                    'file' => $filename,
                    'path' => $path,
                    'data_type' => $dataType,
                ]);

                return [];
            }

            // Attempt to read and parse JSON
            try {
                $contents = File::get($path);
                $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

                Log::debug('Game data loaded successfully', [
                    'file' => $filename,
                    'data_type' => $dataType,
                    'record_count' => is_array($data) ? count($data) : 'N/A',
                ]);

                return $data ?? [];
            } catch (\JsonException $e) {
                Log::error('Failed to parse game data JSON', [
                    'file' => $filename,
                    'path' => $path,
                    'data_type' => $dataType,
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                ]);

                return [];
            } catch (\Exception $e) {
                Log::error('Unexpected error loading game data', [
                    'file' => $filename,
                    'path' => $path,
                    'data_type' => $dataType,
                    'error' => $e->getMessage(),
                ]);

                return [];
            }
        });
    }

    /**
     * Generate a cache key for a given filename.
     *
     * @param string $filename The JSON filename
     * @return string The cache key
     */
    private function getCacheKey(string $filename): string
    {
        return self::CACHE_PREFIX.$filename;
    }

    /**
     * Check if a specific data file exists.
     *
     * @param string $filename The JSON filename
     * @return bool True if the file exists
     */
    public function fileExists(string $filename): bool
    {
        $path = resource_path("json/{$filename}");

        return File::exists($path);
    }

    /**
     * Get the file path for a specific data file.
     *
     * @param string $filename The JSON filename
     * @return string The absolute file path
     */
    public function getFilePath(string $filename): string
    {
        return resource_path("json/{$filename}");
    }
}



