<?php

declare(strict_types=1);

namespace App\Providers;

use Domain\Character\Services\AdvancementOptionsService;
use Domain\Character\Services\AdvancementValidationService;
use Domain\Character\Services\AncestryBonusService;
use Domain\Character\Services\CharacterStatsCalculator;
use Domain\Character\Services\DomainCardService;
use Domain\Character\Services\EquipmentValidator;
use Domain\Character\Services\GameDataLoader;
use Domain\Character\Services\SubclassBonusService;
use Domain\Character\Services\TierAchievementService;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider for Character domain services.
 * 
 * Registers shared services that are used by both CharacterBuilder and CharacterLevelUp
 * to ensure consistent business logic across character creation and leveling.
 * 
 * Services Registered:
 * - AdvancementOptionsService: Determines available advancement options
 * - TierAchievementService: Handles tier achievement logic (levels 2, 5, 8)
 * - DomainCardService: Manages domain card selection and validation
 * - AdvancementValidationService: Validates advancement selections per SRD rules
 * - AncestryBonusService: Calculates ancestry bonuses for character stats
 * - SubclassBonusService: Calculates subclass bonuses for character stats
 * - CharacterStatsCalculator: Orchestrates stat calculations with all bonuses
 * - EquipmentValidator: Validates equipment selections and requirements
 */
class CharacterServicesProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register as singletons for performance and consistency

        // Game data loader (foundational service)
        $this->app->singleton(GameDataLoader::class, function ($app) {
            return new GameDataLoader();
        });

        // Core advancement services
        $this->app->singleton(AdvancementOptionsService::class, function ($app) {
            return new AdvancementOptionsService(
                $app->make(GameDataLoader::class)
            );
        });

        $this->app->singleton(TierAchievementService::class, function ($app) {
            return new TierAchievementService();
        });

        $this->app->singleton(DomainCardService::class, function ($app) {
            return new DomainCardService(
                $app->make(GameDataLoader::class)
            );
        });

        $this->app->singleton(AdvancementValidationService::class, function ($app) {
            return new AdvancementValidationService(
                $app->make(TierAchievementService::class),
                $app->make(DomainCardService::class)
            );
        });

        // Character bonus and calculation services
        $this->app->singleton(AncestryBonusService::class, function ($app) {
            return new AncestryBonusService();
        });

        $this->app->singleton(SubclassBonusService::class, function ($app) {
            return new SubclassBonusService();
        });

        $this->app->singleton(CharacterStatsCalculator::class, function ($app) {
            return new CharacterStatsCalculator(
                $app->make(AncestryBonusService::class),
                $app->make(SubclassBonusService::class)
            );
        });

        $this->app->singleton(EquipmentValidator::class, function ($app) {
            return new EquipmentValidator();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

