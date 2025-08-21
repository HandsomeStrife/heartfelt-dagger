<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Character\Actions;

use Domain\Character\Actions\AssociateCharactersWithUserAction;
use Domain\Character\Models\Character;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AssociateCharactersWithUserActionTest extends TestCase
{
    use RefreshDatabase;

    private AssociateCharactersWithUserAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new AssociateCharactersWithUserAction();
    }

    #[Test]
    public function it_associates_anonymous_characters_with_user(): void
    {
        // Create a user
        $user = User::factory()->create();

        // Create some anonymous characters (user_id = null)
        $character1 = Character::factory()->create(['user_id' => null]);
        $character2 = Character::factory()->create(['user_id' => null]);
        $character3 = Character::factory()->create(['user_id' => null]);

        // Create a character that already belongs to another user
        $another_user = User::factory()->create();
        $character4 = Character::factory()->create(['user_id' => $another_user->id]);

        $character_keys = [
            $character1->character_key,
            $character2->character_key,
            $character3->character_key,
            $character4->character_key, // This should not be associated
        ];

        // Execute the action
        $updated_count = $this->action->execute($user, $character_keys);

        // Assert that only 3 characters were updated (not the one that already had a user)
        $this->assertEquals(3, $updated_count);

        // Assert that the characters are now associated with the user
        $this->assertEquals($user->id, $character1->fresh()->user_id);
        $this->assertEquals($user->id, $character2->fresh()->user_id);
        $this->assertEquals($user->id, $character3->fresh()->user_id);

        // Assert that the character that already had a user is unchanged
        $this->assertEquals($another_user->id, $character4->fresh()->user_id);
    }

    #[Test]
    public function it_returns_zero_when_no_character_keys_provided(): void
    {
        $user = User::factory()->create();

        $updated_count = $this->action->execute($user, []);

        $this->assertEquals(0, $updated_count);
    }

    #[Test]
    public function it_returns_zero_when_no_matching_characters_found(): void
    {
        $user = User::factory()->create();

        $updated_count = $this->action->execute($user, ['non-existent-key-1', 'non-existent-key-2']);

        $this->assertEquals(0, $updated_count);
    }

    #[Test]
    public function it_only_associates_characters_with_null_user_id(): void
    {
        $user = User::factory()->create();
        $another_user = User::factory()->create();

        // Create a character that already belongs to another user
        $existing_character = Character::factory()->create(['user_id' => $another_user->id]);

        $updated_count = $this->action->execute($user, [$existing_character->character_key]);

        // Should not update any characters since it already has a user
        $this->assertEquals(0, $updated_count);

        // Character should still belong to the original user
        $this->assertEquals($another_user->id, $existing_character->fresh()->user_id);
    }

    #[Test]
    public function it_handles_mixed_valid_and_invalid_character_keys(): void
    {
        $user = User::factory()->create();

        // Create some characters
        $anonymous_character = Character::factory()->create(['user_id' => null]);
        $owned_character = Character::factory()->create(['user_id' => $user->id]);

        $character_keys = [
            $anonymous_character->character_key,
            'non-existent-key',
            $owned_character->character_key,
        ];

        $updated_count = $this->action->execute($user, $character_keys);

        // Only the anonymous character should be updated
        $this->assertEquals(1, $updated_count);
        $this->assertEquals($user->id, $anonymous_character->fresh()->user_id);
    }
}
