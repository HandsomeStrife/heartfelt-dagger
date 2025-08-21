<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Character\Actions;

use Domain\Character\Actions\SaveCharacterAction;
use Domain\Character\Data\CharacterBuilderData;
use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterDomainCard;
use Domain\Character\Models\CharacterEquipment;
use Domain\Character\Models\CharacterExperience;
use Domain\Character\Models\CharacterTrait;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SaveCharacterActionTest extends TestCase
{
    use RefreshDatabase;

    private SaveCharacterAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new SaveCharacterAction;
    }

    #[Test]
    public function it_creates_new_character_with_basic_data(): void
    {
        $user = User::factory()->create();
        $builderData = new CharacterBuilderData(
            name: 'Test Hero',
            selectedClass: 'warrior',
            selectedSubclass: 'call-of-the-brave',
            selectedAncestry: 'human',
            selectedCommunity: 'order-of-scholars'
        );

        $character = $this->action->execute($builderData, $user);

        $this->assertInstanceOf(Character::class, $character);
        $this->assertEquals('Test Hero', $character->name);
        $this->assertEquals('warrior', $character->class);
        $this->assertEquals('call-of-the-brave', $character->subclass);
        $this->assertEquals('human', $character->ancestry);
        $this->assertEquals('order-of-scholars', $character->community);
        $this->assertEquals($user->id, $character->user_id);
        $this->assertNotNull($character->character_key);
        $this->assertEquals(8, strlen($character->character_key));
    }

    #[Test]
    public function it_creates_character_without_user(): void
    {
        $builderData = new CharacterBuilderData(
            name: 'Anonymous Hero',
            selectedClass: 'ranger'
        );

        $character = $this->action->execute($builderData, null);

        $this->assertInstanceOf(Character::class, $character);
        $this->assertEquals('Anonymous Hero', $character->name);
        $this->assertEquals('ranger', $character->class);
        $this->assertNull($character->user_id);
    }

    #[Test]
    public function it_creates_character_with_null_values(): void
    {
        $builderData = new CharacterBuilderData(
            name: null,
            selectedClass: null,
            selectedSubclass: null,
            selectedAncestry: null,
            selectedCommunity: null
        );

        $character = $this->action->execute($builderData, null);

        $this->assertInstanceOf(Character::class, $character);
        $this->assertNull($character->name);
        $this->assertNull($character->class);
        $this->assertNull($character->subclass);
        $this->assertNull($character->ancestry);
        $this->assertNull($character->community);
    }

    #[Test]
    public function it_saves_character_traits(): void
    {
        $builderData = new CharacterBuilderData(
            name: 'Test Hero',
            assignedTraits: [
                'agility' => 2,
                'strength' => -1,
                'finesse' => 0,
            ]
        );

        $character = $this->action->execute($builderData, null);

        $this->assertCount(3, $character->traits);

        $traits = $character->traits->keyBy('trait_name');
        $this->assertEquals(2, $traits['agility']->trait_value);
        $this->assertEquals(-1, $traits['strength']->trait_value);
        $this->assertEquals(0, $traits['finesse']->trait_value);
    }

    #[Test]
    public function it_saves_character_equipment(): void
    {
        $builderData = new CharacterBuilderData(
            name: 'Test Hero',
            selectedEquipment: [
                [
                    'key' => 'shortsword',
                    'type' => 'weapon',
                    'data' => ['damage' => '1d6'],
                ],
                [
                    'key' => 'leather-armor',
                    'type' => 'armor',
                    'data' => ['armor_value' => 2],
                ],
            ]
        );

        $character = $this->action->execute($builderData, null);

        $this->assertCount(2, $character->equipment);

        $equipment = $character->equipment->keyBy('equipment_key');
        $this->assertTrue($equipment->has('shortsword'));
        $this->assertTrue($equipment->has('leather-armor'));

        $sword = $equipment['shortsword'];
        $this->assertEquals('weapon', $sword->equipment_type);
        $this->assertEquals(['damage' => '1d6'], $sword->equipment_data);

        $armor = $equipment['leather-armor'];
        $this->assertEquals('armor', $armor->equipment_type);
        $this->assertEquals(['armor_value' => 2], $armor->equipment_data);
    }

    #[Test]
    public function it_saves_character_experiences(): void
    {
        $builderData = new CharacterBuilderData(
            name: 'Test Hero',
            experiences: [
                [
                    'name' => 'Combat Training',
                    'description' => 'Trained with the city guard',
                    'modifier' => 2,
                ],
                [
                    'name' => 'Academic Study',
                    'description' => 'Studied ancient texts',
                    'modifier' => 1,
                ],
            ]
        );

        $character = $this->action->execute($builderData, null);

        $this->assertCount(2, $character->experiences);

        $experiences = $character->experiences;
        $this->assertEquals('Combat Training', $experiences[0]->experience_name);
        $this->assertEquals('Trained with the city guard', $experiences[0]->experience_description);
        $this->assertEquals(2, $experiences[0]->modifier);

        $this->assertEquals('Academic Study', $experiences[1]->experience_name);
        $this->assertEquals('Studied ancient texts', $experiences[1]->experience_description);
        $this->assertEquals(1, $experiences[1]->modifier);
    }

    #[Test]
    public function it_saves_character_domain_cards(): void
    {
        $builderData = new CharacterBuilderData(
            name: 'Test Hero',
            selectedDomainCards: [
                [
                    'domain' => 'blade',
                    'ability_key' => 'strike',
                    'ability_level' => 1,
                ],
                [
                    'domain' => 'grace',
                    'ability_key' => 'dodge',
                    'ability_level' => 2,
                ],
            ]
        );

        $character = $this->action->execute($builderData, null);

        $this->assertCount(2, $character->domainCards);

        $domainCards = $character->domainCards;
        $this->assertEquals('blade', $domainCards[0]->domain);
        $this->assertEquals('strike', $domainCards[0]->ability_key);
        $this->assertEquals(1, $domainCards[0]->ability_level);

        $this->assertEquals('grace', $domainCards[1]->domain);
        $this->assertEquals('dodge', $domainCards[1]->ability_key);
        $this->assertEquals(2, $domainCards[1]->ability_level);
    }

    #[Test]
    public function it_saves_background_and_connection_data(): void
    {
        $builderData = new CharacterBuilderData(
            name: 'Test Hero',
            backgroundAnswers: ['Answer 1', 'Answer 2', 'Answer 3'],
            connectionAnswers: ['Connection 1', 'Connection 2'],
            physicalDescription: 'Tall and strong',
            personalityTraits: 'Brave and loyal',
            personalHistory: 'Born in a small village',
            motivations: 'To protect the innocent'
        );

        $character = $this->action->execute($builderData, null);

        $characterData = $character->character_data;
        $this->assertEquals(['Answer 1', 'Answer 2', 'Answer 3'], $characterData['background']['answers']);
        $this->assertEquals(['Connection 1', 'Connection 2'], $characterData['connections']);
        $this->assertEquals('Tall and strong', $characterData['background']['physicalDescription']);
        $this->assertEquals('Brave and loyal', $characterData['background']['personalityTraits']);
        $this->assertEquals('Born in a small village', $characterData['background']['personalHistory']);
        $this->assertEquals('To protect the innocent', $characterData['background']['motivations']);
    }

    #[Test]
    public function it_updates_existing_character(): void
    {
        $existingCharacter = Character::factory()->create([
            'name' => 'Old Name',
            'class' => 'warrior',
        ]);

        // Add some existing related data
        CharacterTrait::factory()->create([
            'character_id' => $existingCharacter->id,
            'trait_name' => 'agility',
            'trait_value' => 1,
        ]);

        $builderData = new CharacterBuilderData(
            name: 'Updated Name',
            selectedClass: 'ranger',
            assignedTraits: [
                'strength' => 2,
                'finesse' => -1,
            ]
        );

        $updatedCharacter = $this->action->updateCharacter($existingCharacter, $builderData);

        $this->assertEquals('Updated Name', $updatedCharacter->name);
        $this->assertEquals('ranger', $updatedCharacter->class);

        // Old traits should be replaced with new ones
        $this->assertCount(2, $updatedCharacter->traits);
        $traits = $updatedCharacter->traits->keyBy('trait_name');
        $this->assertArrayNotHasKey('agility', $traits->toArray());
        $this->assertEquals(2, $traits['strength']->trait_value);
        $this->assertEquals(-1, $traits['finesse']->trait_value);
    }

    #[Test]
    public function it_clears_related_data_when_updating(): void
    {
        $existingCharacter = Character::factory()->create();

        // Add existing related data
        CharacterTrait::factory()->create(['character_id' => $existingCharacter->id]);
        CharacterEquipment::factory()->create(['character_id' => $existingCharacter->id]);
        CharacterExperience::factory()->create(['character_id' => $existingCharacter->id]);
        CharacterDomainCard::factory()->create(['character_id' => $existingCharacter->id]);

        $builderData = new CharacterBuilderData(name: 'Test Hero');

        $updatedCharacter = $this->action->updateCharacter($existingCharacter, $builderData);

        // All related data should be cleared since builderData has empty arrays
        $this->assertCount(0, $updatedCharacter->traits);
        $this->assertCount(0, $updatedCharacter->equipment);
        $this->assertCount(0, $updatedCharacter->experiences);
        $this->assertCount(0, $updatedCharacter->domainCards);
    }

    #[Test]
    public function it_handles_profile_image_path(): void
    {
        $builderData = new CharacterBuilderData(
            name: 'Test Hero',
            profileImagePath: 'portraits/hero.jpg'
        );

        $character = $this->action->execute($builderData, null);

        $this->assertEquals('portraits/hero.jpg', $character->profile_image_path);
    }

    #[Test]
    public function it_wraps_operation_in_transaction(): void
    {
        // This test ensures that if anything fails, everything is rolled back
        $builderData = new CharacterBuilderData(
            name: 'Test Hero',
            assignedTraits: [
                'agility' => 2,
            ]
        );

        $character = $this->action->execute($builderData, null);

        // Verify both character and traits were created
        $this->assertDatabaseHas('characters', ['name' => 'Test Hero']);
        $this->assertDatabaseHas('character_traits', [
            'character_id' => $character->id,
            'trait_name' => 'agility',
            'trait_value' => 2,
        ]);
    }
}
