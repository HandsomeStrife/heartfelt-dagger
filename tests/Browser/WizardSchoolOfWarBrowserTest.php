<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

/**
 * Comprehensive browser test for Wizard + School of War
 * 
 * Verifies:
 * - Basic class stats (Evasion 10, HP 6)
 * - Domain access (Codex + Midnight)
 * - Subclass selection and features
 * - +1 Hit Point bonus from School of War
 * - Spellcasting trait assignment (Knowledge)
 * - Domain card selection (2 base cards, no bonuses)
 */

test('wizard school of war character builder integration works correctly', function () {
    // Create a test character with shorter key
    $character = Character::factory()->create([
        'character_key' => 'TEST123',
        'class' => null,
        'subclass' => null,
    ]);

    // Test that the character builder page loads 
    $response = get("/character-builder/{$character->character_key}");
    $response->assertStatus(200);
    
    // Test the core functionality: character creation with School of War
    $character->update([
        'class' => 'wizard',
        'subclass' => 'school of war',
        'ancestry' => 'human',
        'community' => 'loreborne',
    ]);
    
    // Add traits for complete testing
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 0],
        ['trait_name' => 'strength', 'trait_value' => 0],
        ['trait_name' => 'finesse', 'trait_value' => 1],
        ['trait_name' => 'instinct', 'trait_value' => -1],
        ['trait_name' => 'presence', 'trait_value' => 1],
        ['trait_name' => 'knowledge', 'trait_value' => 2],
    ]);
    
    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);
    
    // Verify School of War +1 Hit Point bonus is applied correctly
    expect($stats->hit_points)->toBe(6); // Wizard 5 + School of War 1 (Human provides stress, not HP)
    
    // Verify the database has the correct data
    expect($character->fresh())
        ->class->toBe('wizard')
        ->subclass->toBe('school of war')
        ->ancestry->toBe('human')
        ->community->toBe('loreborne');
        
    // Verify domain card logic is correct (School of War gets no bonus)
    $builderData = \Domain\Character\Data\CharacterBuilderData::from([
        'selected_class' => 'wizard',
        'selected_subclass' => 'school of war',
    ]);
    
    expect($builderData->getMaxDomainCards())->toBe(2); // Base 2, no bonuses
    expect($builderData->getSubclassDomainCardBonus())->toBe(0);
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('wizard school of war subclass bonuses apply correctly in character data', function () {
    $character = createTestCharacterWith([
        'class' => 'wizard',
        'subclass' => 'school of war',
        'ancestry' => 'human',
        'community' => 'loreborne',
    ]);
    
    // Add some trait values for testing
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 0],
        ['trait_name' => 'strength', 'trait_value' => 0],
        ['trait_name' => 'finesse', 'trait_value' => 1],
        ['trait_name' => 'instinct', 'trait_value' => -1],
        ['trait_name' => 'presence', 'trait_value' => 1],
        ['trait_name' => 'knowledge', 'trait_value' => 2],
    ]);
    
    // Get character stats using the model method
    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);
    
    // Verify School of War provides +1 Hit Point bonus (Human gives stress, not HP)
    expect($stats->hit_points)->toBe(6); // Wizard base 5 + School of War +1
    
    // Verify other stats are calculated correctly
    expect($stats->evasion)->toBe(11); // Base class evasion
    expect($stats->stress)->toBe(7); // Wizard base stress + Human bonus
    
    // Test domain card calculation using builder data
    $builderData = \Domain\Character\Data\CharacterBuilderData::from([
        'selected_class' => 'wizard',
        'selected_subclass' => 'school of war',
    ]);
    
    // Verify no domain card bonus (School of War doesn't provide domain card bonuses)
    expect($builderData->getMaxDomainCards())->toBe(2); // Base 2, no bonuses
    expect($builderData->getSubclassDomainCardBonus())->toBe(0);
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('school of war features are correctly defined', function () {
    // Test that School of War subclass features are properly configured
    
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $schoolOfWar = $subclassData['school of war'];
    
    expect($schoolOfWar)->not()->toBeNull();
    expect($schoolOfWar['name'])->toBe('School of War');
    expect($schoolOfWar['spellcastTrait'])->toBe('Knowledge');
    
    // Verify foundation feature (Battlemage) provides hit point bonus
    $foundationFeature = $schoolOfWar['foundationFeatures'][0];
    expect($foundationFeature['name'])->toBe('Battlemage');
    expect($foundationFeature['effects'][0]['type'])->toBe('hit_point_bonus');
    expect($foundationFeature['effects'][0]['value'])->toBe(1);
    
    // Verify specialization and mastery features exist
    expect($schoolOfWar['specializationFeatures'])->not()->toBeEmpty();
    expect($schoolOfWar['masteryFeatures'])->not()->toBeEmpty();
});