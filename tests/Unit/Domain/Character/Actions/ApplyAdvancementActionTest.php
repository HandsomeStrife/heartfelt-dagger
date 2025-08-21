<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Character\Actions;

use Domain\Character\Actions\ApplyAdvancementAction;
use Domain\Character\Data\CharacterAdvancementData;
use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterAdvancement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplyAdvancementActionTest extends TestCase
{
    use RefreshDatabase;

    private ApplyAdvancementAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new ApplyAdvancementAction();
    }

    #[Test]
    public function execute_creates_new_advancement_record(): void
    {
        $character = Character::factory()->create();
        $advancement_data = CharacterAdvancementData::traitBonus(1, 1, ['agility'], 1);

        $result = $this->action->execute($character, $advancement_data);

        $this->assertInstanceOf(CharacterAdvancement::class, $result);
        $this->assertEquals($character->id, $result->character_id);
        $this->assertEquals(1, $result->tier);
        $this->assertEquals(1, $result->advancement_number);
        $this->assertEquals('trait_bonus', $result->advancement_type);
        $this->assertEquals(['traits' => ['agility'], 'bonus' => 1], $result->advancement_data);
    }

    #[Test]
    public function execute_throws_exception_for_duplicate_advancement_slot(): void
    {
        $character = Character::factory()->create();
        
        // Create existing advancement
        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'tier' => 1,
            'advancement_number' => 1,
        ]);

        $advancement_data = CharacterAdvancementData::traitBonus(1, 1, ['agility'], 1);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Advancement slot already taken');

        $this->action->execute($character, $advancement_data);
    }

    #[Test]
    public function execute_allows_different_advancement_numbers_same_tier(): void
    {
        $character = Character::factory()->create();
        
        // Create first advancement
        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'tier' => 1,
            'advancement_number' => 1,
        ]);

        $advancement_data = CharacterAdvancementData::hitPoint(1, 2);

        $result = $this->action->execute($character, $advancement_data);

        $this->assertInstanceOf(CharacterAdvancement::class, $result);
        $this->assertEquals(1, $result->tier);
        $this->assertEquals(2, $result->advancement_number);
    }

    #[Test]
    public function execute_allows_same_advancement_number_different_tier(): void
    {
        $character = Character::factory()->create();
        
        // Create first advancement
        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'tier' => 1,
            'advancement_number' => 1,
        ]);

        $advancement_data = CharacterAdvancementData::stress(2, 1);

        $result = $this->action->execute($character, $advancement_data);

        $this->assertInstanceOf(CharacterAdvancement::class, $result);
        $this->assertEquals(2, $result->tier);
        $this->assertEquals(1, $result->advancement_number);
    }

    #[Test]
    public function execute_validates_tier_range(): void
    {
        $character = Character::factory()->create();
        $advancement_data = CharacterAdvancementData::traitBonus(0, 1, ['agility'], 1); // Invalid tier

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Tier must be between 1 and 4');

        $this->action->execute($character, $advancement_data);
    }

    #[Test]
    public function execute_validates_tier_upper_bound(): void
    {
        $character = Character::factory()->create();
        $advancement_data = CharacterAdvancementData::traitBonus(5, 1, ['agility'], 1); // Invalid tier

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Tier must be between 1 and 4');

        $this->action->execute($character, $advancement_data);
    }

    #[Test]
    public function execute_validates_advancement_number_range(): void
    {
        $character = Character::factory()->create();
        $advancement_data = CharacterAdvancementData::traitBonus(1, 0, ['agility'], 1); // Invalid advancement number

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Advancement number must be 1 or 2');

        $this->action->execute($character, $advancement_data);
    }

    #[Test]
    public function execute_validates_advancement_number_upper_bound(): void
    {
        $character = Character::factory()->create();
        $advancement_data = CharacterAdvancementData::traitBonus(1, 3, ['agility'], 1); // Invalid advancement number

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Advancement number must be 1 or 2');

        $this->action->execute($character, $advancement_data);
    }

    #[Test]
    public function execute_validates_character_level_for_tier(): void
    {
        $character = Character::factory()->create(['level' => 1]);
        $advancement_data = CharacterAdvancementData::traitBonus(3, 1, ['agility'], 1); // Tier 3 requires level 5+

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Character level insufficient for tier 3');

        $this->action->execute($character, $advancement_data);
    }

    #[Test]
    public function execute_allows_tier_progression_with_sufficient_level(): void
    {
        $character = Character::factory()->create(['level' => 5]);
        $advancement_data = CharacterAdvancementData::traitBonus(3, 1, ['agility'], 1);

        $result = $this->action->execute($character, $advancement_data);

        $this->assertInstanceOf(CharacterAdvancement::class, $result);
        $this->assertEquals(3, $result->tier);
    }

    #[Test]
    public function execute_validates_multiclass_advancement_data(): void
    {
        $character = Character::factory()->create(['level' => 7]); // Level for tier 4
        $advancement_data = CharacterAdvancementData::multiclass(4, 1, ''); // Empty class

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Multiclass advancement requires a class selection');

        $this->action->execute($character, $advancement_data);
    }

    #[Test]
    public function execute_validates_trait_bonus_advancement_data(): void
    {
        $character = Character::factory()->create();
        $advancement_data = CharacterAdvancementData::traitBonus(1, 1, [], 1); // Empty traits array

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Trait bonus advancement requires at least one trait');

        $this->action->execute($character, $advancement_data);
    }

    #[Test]
    public function execute_creates_advancement_in_transaction(): void
    {
        $character = Character::factory()->create();
        $advancement_data = CharacterAdvancementData::traitBonus(1, 1, ['agility'], 1);

        // Mock database transaction to test rollback behavior
        DB::shouldReceive('transaction')
            ->once()
            ->with(\Closure::class)
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $result = $this->action->execute($character, $advancement_data);

        $this->assertInstanceOf(CharacterAdvancement::class, $result);
    }

    #[Test]
    public function execute_creates_different_advancement_types(): void
    {
        $character = Character::factory()->create(['level' => 7]);

        $advancement_types = [
            CharacterAdvancementData::traitBonus(1, 1, ['agility'], 1),
            CharacterAdvancementData::hitPoint(1, 2),
            CharacterAdvancementData::stress(2, 1),
            CharacterAdvancementData::experienceBonus(2, 2),
            CharacterAdvancementData::domainCard(3, 1, 2),
            CharacterAdvancementData::evasion(3, 2),
            CharacterAdvancementData::subclass(4, 1, 'upgraded'),
            CharacterAdvancementData::proficiency(4, 2),
        ];

        foreach ($advancement_types as $advancement_data) {
            $result = $this->action->execute($character, $advancement_data);
            
            $this->assertInstanceOf(CharacterAdvancement::class, $result);
            $this->assertEquals($advancement_data->advancement_type, $result->advancement_type);
            $this->assertEquals($advancement_data->advancement_data, $result->advancement_data);
        }

        // Should have 8 total advancements
        $this->assertCount(8, $character->fresh()->advancements);
    }

    #[Test]
    public function execute_persists_advancement_to_database(): void
    {
        $character = Character::factory()->create();
        $advancement_data = CharacterAdvancementData::evasion(2, 1);

        $result = $this->action->execute($character, $advancement_data);

        // Verify it's actually in the database
        $this->assertDatabaseHas('character_advancements', [
            'id' => $result->id,
            'character_id' => $character->id,
            'tier' => 2,
            'advancement_number' => 1,
            'advancement_type' => 'evasion',
        ]);
    }
}
