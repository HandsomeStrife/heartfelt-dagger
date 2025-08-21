<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Character\Actions;

use Domain\Character\Actions\LoadCharacterAction;
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

class LoadCharacterActionTest extends TestCase
{
    use RefreshDatabase;

    private LoadCharacterAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new LoadCharacterAction;
    }

    #[Test]
    public function it_returns_null_for_non_existent_character(): void
    {
        $result = $this->action->execute('NOTEXIST');

        $this->assertNull($result);
    }

    #[Test]
    public function it_loads_basic_character_data(): void
    {
        $character = Character::factory()->create([
            'character_key' => 'ABC12345',
            'name' => 'Test Hero',
            'class' => 'warrior',
            'subclass' => 'call-of-the-brave',
            'ancestry' => 'human',
            'community' => 'order-of-scholars',
            'profile_image_path' => 'hero.jpg',
        ]);

        $result = $this->action->execute('ABC12345');

        $this->assertInstanceOf(CharacterBuilderData::class, $result);
        $this->assertEquals('Test Hero', $result->name);
        $this->assertEquals('warrior', $result->selectedClass);
        $this->assertEquals('call-of-the-brave', $result->selectedSubclass);
        $this->assertEquals('human', $result->selectedAncestry);
        $this->assertEquals('order-of-scholars', $result->selectedCommunity);
        $this->assertEquals('hero.jpg', $result->profileImagePath);
    }

    #[Test]
    public function it_loads_character_with_null_values(): void
    {
        $character = Character::factory()->create([
            'character_key' => 'ABC12345',
            'name' => null,
            'class' => null,
            'subclass' => null,
            'ancestry' => null,
            'community' => null,
        ]);

        $result = $this->action->execute('ABC12345');

        $this->assertInstanceOf(CharacterBuilderData::class, $result);
        $this->assertNull($result->name);
        $this->assertNull($result->selectedClass);
        $this->assertNull($result->selectedSubclass);
        $this->assertNull($result->selectedAncestry);
        $this->assertNull($result->selectedCommunity);
    }

    #[Test]
    public function it_loads_character_traits(): void
    {
        $character = Character::factory()->create(['character_key' => 'ABC12345']);

        CharacterTrait::factory()->create([
            'character_id' => $character->id,
            'trait_name' => 'agility',
            'trait_value' => 2,
        ]);

        CharacterTrait::factory()->create([
            'character_id' => $character->id,
            'trait_name' => 'strength',
            'trait_value' => -1,
        ]);

        $result = $this->action->execute('ABC12345');

        $this->assertEquals(['agility' => 2, 'strength' => -1], $result->assignedTraits);
    }

    #[Test]
    public function it_loads_character_equipment(): void
    {
        $character = Character::factory()->create(['character_key' => 'ABC12345']);

        CharacterEquipment::factory()->create([
            'character_id' => $character->id,
            'equipment_type' => 'weapon',
            'equipment_key' => 'shortsword',
            'equipment_data' => ['damage' => '1d6'],
        ]);

        $result = $this->action->execute('ABC12345');

        $this->assertCount(1, $result->selectedEquipment);
        $this->assertEquals('shortsword', $result->selectedEquipment[0]['key']);
        $this->assertEquals('weapon', $result->selectedEquipment[0]['type']);
        $this->assertEquals(['damage' => '1d6'], $result->selectedEquipment[0]['data']);
    }

    #[Test]
    public function it_loads_character_domain_cards(): void
    {
        $character = Character::factory()->create(['character_key' => 'ABC12345']);

        CharacterDomainCard::factory()->create([
            'character_id' => $character->id,
            'domain' => 'blade',
            'ability_key' => 'strike',
            'ability_level' => 1,
        ]);

        $result = $this->action->execute('ABC12345');

        $this->assertCount(1, $result->selectedDomainCards);
        $this->assertEquals('blade', $result->selectedDomainCards[0]['domain']);
        $this->assertEquals('strike', $result->selectedDomainCards[0]['ability_key']);
        $this->assertEquals(1, $result->selectedDomainCards[0]['ability_level']);
    }

    #[Test]
    public function it_loads_character_experiences(): void
    {
        $character = Character::factory()->create(['character_key' => 'ABC12345']);

        CharacterExperience::factory()->create([
            'character_id' => $character->id,
            'experience_name' => 'Combat Training',
            'experience_description' => 'Trained with the city guard',
            'modifier' => 2,
        ]);

        $result = $this->action->execute('ABC12345');

        $this->assertCount(1, $result->experiences);
        $this->assertEquals('Combat Training', $result->experiences[0]['name']);
        $this->assertEquals('Trained with the city guard', $result->experiences[0]['description']);
        $this->assertEquals(2, $result->experiences[0]['modifier']);
    }

    #[Test]
    public function it_loads_character_background_and_connection_data(): void
    {
        $characterData = [
            'background' => [
                'answers' => ['Answer 1', 'Answer 2', 'Answer 3'],
            ],
            'connections' => ['Connection 1', 'Connection 2'],
        ];

        $character = Character::factory()->create([
            'character_key' => 'ABC12345',
            'character_data' => $characterData,
        ]);

        $result = $this->action->execute('ABC12345');

        $this->assertEquals(['Answer 1', 'Answer 2', 'Answer 3'], $result->backgroundAnswers);
        $this->assertEquals(['Connection 1', 'Connection 2'], $result->connectionAnswers);
    }

    #[Test]
    public function it_handles_empty_character_data(): void
    {
        $character = Character::factory()->create([
            'character_key' => 'ABC12345',
            'character_data' => [],
        ]);

        $result = $this->action->execute('ABC12345');

        $this->assertEquals([], $result->backgroundAnswers);
        $this->assertEquals([], $result->connectionAnswers);
    }

    #[Test]
    public function it_loads_character_by_id(): void
    {
        $character = Character::factory()->create([
            'name' => 'Test Hero by ID',
            'class' => 'ranger',
        ]);

        $result = $this->action->executeById($character->id);

        $this->assertInstanceOf(CharacterBuilderData::class, $result);
        $this->assertEquals('Test Hero by ID', $result->name);
        $this->assertEquals('ranger', $result->selectedClass);
    }

    #[Test]
    public function it_returns_null_for_non_existent_id(): void
    {
        $result = $this->action->executeById(99999);

        $this->assertNull($result);
    }

    #[Test]
    public function it_loads_user_characters(): void
    {
        $user = User::factory()->create();
        $character1 = Character::factory()->create(['user_id' => $user->id, 'name' => 'Hero 1']);
        $character2 = Character::factory()->create(['user_id' => $user->id, 'name' => 'Hero 2']);
        Character::factory()->create(['user_id' => null, 'name' => 'Other Hero']); // Different user

        $result = $this->action->loadForUser($user->id);

        $this->assertCount(2, $result);
        $this->assertEquals('Hero 1', $result[0]['name']);
        $this->assertEquals('Hero 2', $result[1]['name']);
    }

    #[Test]
    public function it_loads_public_characters(): void
    {
        Character::factory()->create(['is_public' => true, 'name' => 'Public Hero 1']);
        Character::factory()->create(['is_public' => true, 'name' => 'Public Hero 2']);
        Character::factory()->create(['is_public' => false, 'name' => 'Private Hero']);

        $result = $this->action->loadPublicCharacters();

        $this->assertCount(2, $result);
        $this->assertEquals('Public Hero 1', $result[0]['name']);
        $this->assertEquals('Public Hero 2', $result[1]['name']);
    }

    #[Test]
    public function it_respects_limit_for_public_characters(): void
    {
        Character::factory()->count(5)->create(['is_public' => true]);

        $result = $this->action->loadPublicCharacters(3);

        $this->assertCount(3, $result);
    }
}
