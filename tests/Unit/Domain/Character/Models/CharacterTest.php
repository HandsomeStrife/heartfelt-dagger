<?php

declare(strict_types=1);
use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterDomainCard;
use Domain\Character\Models\CharacterEquipment;
use Domain\Character\Models\CharacterExperience;
use Domain\Character\Models\CharacterTrait;
use Domain\User\Models\User;
use PHPUnit\Framework\Attributes\Test;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('generates unique character key on creation', function () {
    $character = Character::factory()->create();

    expect($character->character_key)->not->toBeNull();
    expect(strlen($character->character_key))->toEqual(10);
    expect($character->character_key)->toMatch('/^[A-Z0-9]{10}$/');
});
it('ensures character keys are unique', function () {
    $character1 = Character::factory()->create();
    $character2 = Character::factory()->create();

    expect($character1->character_key)->not->toEqual($character2->character_key);
});
it('belongs to a user', function () {
    $user = User::factory()->create();
    $character = Character::factory()->forUser($user)->create();

    expect($character->user)->toBeInstanceOf(User::class);
    expect($character->user->id)->toEqual($user->id);
});
it('can have no user', function () {
    $character = Character::factory()->create(['user_id' => null]);

    expect($character->user)->toBeNull();
});
it('has many traits', function () {
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

    expect($character->traits)->toHaveCount(2);
    expect($character->traits->first()->trait_name)->toEqual('agility');
});
it('has many equipment', function () {
    $character = Character::factory()->create();

    CharacterEquipment::factory()->create([
        'character_id' => $character->id,
        'equipment_type' => 'weapon',
        'equipment_key' => 'shortsword',
    ]);

    expect($character->equipment)->toHaveCount(1);
    expect($character->equipment->first()->equipment_type)->toEqual('weapon');
});
it('has many domain cards', function () {
    $character = Character::factory()->create();

    CharacterDomainCard::factory()->create([
        'character_id' => $character->id,
        'domain' => 'blade',
        'ability_key' => 'strike',
    ]);

    expect($character->domainCards)->toHaveCount(1);
    expect($character->domainCards->first()->domain)->toEqual('blade');
});
it('has many experiences', function () {
    $character = Character::factory()->create();

    CharacterExperience::factory()->create([
        'character_id' => $character->id,
        'experience_name' => 'Combat Training',
    ]);

    expect($character->experiences)->toHaveCount(1);
    expect($character->experiences->first()->experience_name)->toEqual('Combat Training');
});
it('casts character data to array', function () {
    $data = [
        'background' => ['answers' => ['Answer 1', 'Answer 2']],
        'connections' => ['Connection 1', 'Connection 2'],
    ];

    $character = Character::factory()->create([
        'character_data' => $data,
    ]);

    expect($character->character_data)->toBeArray();
    expect($character->character_data)->toEqual($data);
});
it('casts is public to boolean', function () {
    $character = Character::factory()->create(['is_public' => 1]);
    expect($character->is_public)->toBeBool();
    expect($character->is_public)->toBeTrue();

    $character = Character::factory()->create(['is_public' => 0]);
    expect($character->is_public)->toBeBool();
    expect($character->is_public)->toBeFalse();
});
it('scopes public characters', function () {
    // Clear existing characters to ensure test isolation
    Character::query()->delete();

    Character::factory()->create(['is_public' => true]);
    Character::factory()->create(['is_public' => false]);
    Character::factory()->create(['is_public' => true]);

    $publicCharacters = Character::public()->get();

    expect($publicCharacters)->toHaveCount(2);
    expect($publicCharacters->every(fn ($char) => $char->is_public))->toBeTrue();
});
it('generates share url', function () {
    $character = Character::factory()->create(['character_key' => 'ABC12345']);

    $shareUrl = $character->getShareUrl();

    expect($shareUrl)->toContain('/character/ABC12345');
});
it('generates banner url', function () {
    $character = Character::factory()->create(['class' => 'warrior']);

    $banner = $character->getBanner();

    expect($banner)->toContain('img/banners/warrior.webp');
});
it('returns default profile image when s3 file not found', function () {
    $character = Character::factory()->create([
        'profile_image_path' => 'character-portraits/hero.jpg',
    ]);

    $profileImage = $character->getProfileImage();

    // Since S3 storage doesn't exist in test environment, should return default avatar
    expect($profileImage)->toContain('img/default-avatar.png');
});
it('returns default profile image when none set', function () {
    $character = Character::factory()->create(['profile_image_path' => null]);

    $profileImage = $character->getProfileImage();

    expect($profileImage)->toContain('img/default-avatar.png');
});
it('validates required fields', function () {
    expect(fn () => Character::create([
        // Missing required fields like character_key, name, class, etc.
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});
it('can be created with complete factory', function () {
    $character = Character::factory()->complete()->create();

    expect($character->traits)->toHaveCount(6);
    // All 6 traits
    expect($character->equipment->count())->toBeGreaterThanOrEqual(2);
    expect($character->experiences)->toHaveCount(2);
    expect($character->domainCards)->toHaveCount(2);
});
it('has fillable attributes', function () {
    $fillable = [
        'character_key',
        'public_key',
        'user_id',
        'name',
        'pronouns',
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

    expect($character->getFillable())->toEqual($fillable);
});
