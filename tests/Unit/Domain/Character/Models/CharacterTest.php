<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Character\Models;

use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterDomainCard;
use Domain\Character\Models\CharacterEquipment;
use Domain\Character\Models\CharacterExperience;
use Domain\Character\Models\CharacterTrait;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CharacterTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_generates_unique_character_key_on_creation(): void
    {
        $character = Character::factory()->create();

        $this->assertNotNull($character->character_key);
        $this->assertEquals(8, strlen($character->character_key));
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{8}$/', $character->character_key);
    }

    #[Test]
    public function it_ensures_character_keys_are_unique(): void
    {
        $character1 = Character::factory()->create();
        $character2 = Character::factory()->create();

        $this->assertNotEquals($character1->character_key, $character2->character_key);
    }

    #[Test]
    public function it_belongs_to_a_user(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->forUser($user)->create();

        $this->assertInstanceOf(User::class, $character->user);
        $this->assertEquals($user->id, $character->user->id);
    }

    #[Test]
    public function it_can_have_no_user(): void
    {
        $character = Character::factory()->create(['user_id' => null]);

        $this->assertNull($character->user);
    }

    #[Test]
    public function it_has_many_traits(): void
    {
        $character = Character::factory()->create();

        CharacterTrait::factory()->create([
            'character_id' => $character->id,
            'trait_name' => 'agility',
            'trait_value' => 2,
        ]);

        CharacterTrait::factory()->create([
            'character_id' => $character->id,
            'trait_name' => 'strength',
            'trait_value' => 1,
        ]);

        $this->assertCount(2, $character->traits);
        $this->assertEquals('agility', $character->traits->first()->trait_name);
    }

    #[Test]
    public function it_has_many_equipment(): void
    {
        $character = Character::factory()->create();

        CharacterEquipment::factory()->create([
            'character_id' => $character->id,
            'equipment_type' => 'weapon',
            'equipment_key' => 'shortsword',
        ]);

        $this->assertCount(1, $character->equipment);
        $this->assertEquals('weapon', $character->equipment->first()->equipment_type);
    }

    #[Test]
    public function it_has_many_domain_cards(): void
    {
        $character = Character::factory()->create();

        CharacterDomainCard::factory()->create([
            'character_id' => $character->id,
            'domain' => 'blade',
            'ability_key' => 'strike',
        ]);

        $this->assertCount(1, $character->domainCards);
        $this->assertEquals('blade', $character->domainCards->first()->domain);
    }

    #[Test]
    public function it_has_many_experiences(): void
    {
        $character = Character::factory()->create();

        CharacterExperience::factory()->create([
            'character_id' => $character->id,
            'experience_name' => 'Combat Training',
        ]);

        $this->assertCount(1, $character->experiences);
        $this->assertEquals('Combat Training', $character->experiences->first()->experience_name);
    }

    #[Test]
    public function it_casts_character_data_to_array(): void
    {
        $data = [
            'background' => ['answers' => ['Answer 1', 'Answer 2']],
            'connections' => ['Connection 1', 'Connection 2'],
        ];

        $character = Character::factory()->create([
            'character_data' => $data,
        ]);

        $this->assertIsArray($character->character_data);
        $this->assertEquals($data, $character->character_data);
    }

    #[Test]
    public function it_casts_is_public_to_boolean(): void
    {
        $character = Character::factory()->create(['is_public' => 1]);
        $this->assertIsBool($character->is_public);
        $this->assertTrue($character->is_public);

        $character = Character::factory()->create(['is_public' => 0]);
        $this->assertIsBool($character->is_public);
        $this->assertFalse($character->is_public);
    }

    #[Test]
    public function it_scopes_public_characters(): void
    {
        Character::factory()->create(['is_public' => true]);
        Character::factory()->create(['is_public' => false]);
        Character::factory()->create(['is_public' => true]);

        $publicCharacters = Character::public()->get();

        $this->assertCount(2, $publicCharacters);
        $this->assertTrue($publicCharacters->every(fn ($char) => $char->is_public));
    }

    #[Test]
    public function it_generates_share_url(): void
    {
        $character = Character::factory()->create(['character_key' => 'ABC12345']);

        $shareUrl = $character->getShareUrl();

        $this->assertStringContainsString('/character/ABC12345', $shareUrl);
    }

    #[Test]
    public function it_generates_banner_url(): void
    {
        $character = Character::factory()->create(['class' => 'warrior']);

        $banner = $character->getBanner();

        $this->assertStringContainsString('img/banners/warrior.webp', $banner);
    }

    #[Test]
    public function it_generates_profile_image_url(): void
    {
        $character = Character::factory()->create([
            'profile_image_path' => 'character-portraits/hero.jpg',
        ]);

        $profileImage = $character->getProfileImage();

        $this->assertStringContainsString('storage/character-portraits/hero.jpg', $profileImage);
    }

    #[Test]
    public function it_returns_default_profile_image_when_none_set(): void
    {
        $character = Character::factory()->create(['profile_image_path' => null]);

        $profileImage = $character->getProfileImage();

        $this->assertStringContainsString('img/default-avatar.png', $profileImage);
    }

    #[Test]
    public function it_validates_required_fields(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Character::create([
            // Missing required fields like character_key, name, class, etc.
        ]);
    }

    #[Test]
    public function it_can_be_created_with_complete_factory(): void
    {
        $character = Character::factory()->complete()->create();

        $this->assertCount(6, $character->traits); // All 6 traits
        $this->assertGreaterThanOrEqual(2, $character->equipment->count());
        $this->assertCount(2, $character->experiences);
        $this->assertCount(2, $character->domainCards);
    }

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $fillable = [
            'character_key',
            'user_id',
            'name',
            'class',
            'subclass',
            'ancestry',
            'community',
            'level',
            'profile_image_path',
            'character_data',
            'is_public',
        ];

        $character = new Character;

        $this->assertEquals($fillable, $character->getFillable());
    }
}
