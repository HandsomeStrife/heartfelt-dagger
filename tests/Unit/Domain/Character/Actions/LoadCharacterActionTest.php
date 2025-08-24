<?php

declare(strict_types=1);
use Domain\Character\Actions\LoadCharacterAction;
use Domain\Character\Data\CharacterBuilderData;
use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterDomainCard;
use Domain\Character\Models\CharacterEquipment;
use Domain\Character\Models\CharacterExperience;
use Domain\Character\Models\CharacterTrait;
use Domain\User\Models\User;
use PHPUnit\Framework\Attributes\Test;
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->action = new LoadCharacterAction;
});
it('returns null for non existent character', function () {
    $result = $this->action->execute('NOTEXIST');

    expect($result)->toBeNull();
});
it('loads basic character data', function () {
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

    expect($result)->toBeInstanceOf(CharacterBuilderData::class);
    expect($result->name)->toEqual('Test Hero');
    expect($result->selected_class)->toEqual('warrior');
    expect($result->selected_subclass)->toEqual('call-of-the-brave');
    expect($result->selected_ancestry)->toEqual('human');
    expect($result->selected_community)->toEqual('order-of-scholars');
    expect($result->profile_image_path)->toEqual('hero.jpg');
});
it('loads character with null values', function () {
    $character = Character::factory()->create([
        'character_key' => 'ABC12345',
        'name' => null,
        'class' => null,
        'subclass' => null,
        'ancestry' => null,
        'community' => null,
    ]);

    $result = $this->action->execute('ABC12345');

    expect($result)->toBeInstanceOf(CharacterBuilderData::class);
    expect($result->name)->toBeNull();
    expect($result->selected_class)->toBeNull();
    expect($result->selected_subclass)->toBeNull();
    expect($result->selected_ancestry)->toBeNull();
    expect($result->selected_community)->toBeNull();
});
it('loads character traits', function () {
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

    expect($result->assigned_traits)->toEqual(['agility' => 2, 'strength' => -1]);
});
it('loads character equipment', function () {
    $character = Character::factory()->create(['character_key' => 'ABC12345']);

    CharacterEquipment::factory()->create([
        'character_id' => $character->id,
        'equipment_type' => 'weapon',
        'equipment_key' => 'shortsword',
        'equipment_data' => ['damage' => '1d6'],
    ]);

    $result = $this->action->execute('ABC12345');

    expect($result->selected_equipment)->toHaveCount(1);
    expect($result->selected_equipment[0]['key'])->toEqual('shortsword');
    expect($result->selected_equipment[0]['type'])->toEqual('weapon');
    expect($result->selected_equipment[0]['data'])->toEqual(['damage' => '1d6']);
});
it('loads character domain cards', function () {
    $character = Character::factory()->create(['character_key' => 'ABC12345']);

    CharacterDomainCard::factory()->create([
        'character_id' => $character->id,
        'domain' => 'blade',
        'ability_key' => 'strike',
        'ability_level' => 1,
    ]);

    $result = $this->action->execute('ABC12345');

    expect($result->selected_domain_cards)->toHaveCount(1);
    expect($result->selected_domain_cards[0]['domain'])->toEqual('blade');
    expect($result->selected_domain_cards[0]['ability_key'])->toEqual('strike');
    expect($result->selected_domain_cards[0]['ability_level'])->toEqual(1);
});
it('loads character experiences', function () {
    $character = Character::factory()->create(['character_key' => 'ABC12345']);

    CharacterExperience::factory()->create([
        'character_id' => $character->id,
        'experience_name' => 'Combat Training',
        'experience_description' => 'Trained with the city guard',
        'modifier' => 2,
    ]);

    $result = $this->action->execute('ABC12345');

    expect($result->experiences)->toHaveCount(1);
    expect($result->experiences[0]['name'])->toEqual('Combat Training');
    expect($result->experiences[0]['description'])->toEqual('Trained with the city guard');
    expect($result->experiences[0]['modifier'])->toEqual(2);
});
it('loads character background and connection data', function () {
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

    expect($result->background_answers)->toEqual(['Answer 1', 'Answer 2', 'Answer 3']);
    expect($result->connection_answers)->toEqual(['Connection 1', 'Connection 2']);
});
it('handles empty character data', function () {
    $character = Character::factory()->create([
        'character_key' => 'ABC12345',
        'character_data' => [],
    ]);

    $result = $this->action->execute('ABC12345');

    expect($result->background_answers)->toEqual([]);
    expect($result->connection_answers)->toEqual([]);
});
it('loads character by id', function () {
    $character = Character::factory()->create([
        'name' => 'Test Hero by ID',
        'class' => 'ranger',
    ]);

    $result = $this->action->executeById($character->id);

    expect($result)->toBeInstanceOf(CharacterBuilderData::class);
    expect($result->name)->toEqual('Test Hero by ID');
    expect($result->selected_class)->toEqual('ranger');
});
it('returns null for non existent id', function () {
    $result = $this->action->executeById(99999);

    expect($result)->toBeNull();
});
it('loads user characters', function () {
    $user = User::factory()->create();
    $character1 = Character::factory()->create(['user_id' => $user->id, 'name' => 'Hero 1']);
    $character2 = Character::factory()->create(['user_id' => $user->id, 'name' => 'Hero 2']);
    Character::factory()->create(['user_id' => null, 'name' => 'Other Hero']);

    // Different user
    $result = $this->action->loadForUser($user->id);

    expect($result)->toHaveCount(2);
    expect($result[0]['name'])->toEqual('Hero 1');
    expect($result[1]['name'])->toEqual('Hero 2');
});
it('loads public characters', function () {
    Character::factory()->create(['is_public' => true, 'name' => 'Public Hero 1']);
    Character::factory()->create(['is_public' => true, 'name' => 'Public Hero 2']);
    Character::factory()->create(['is_public' => false, 'name' => 'Private Hero']);

    $result = $this->action->loadPublicCharacters();

    expect($result)->toHaveCount(2);
    expect($result[0]['name'])->toEqual('Public Hero 1');
    expect($result[1]['name'])->toEqual('Public Hero 2');
});
it('respects limit for public characters', function () {
    Character::factory()->count(5)->create(['is_public' => true]);

    $result = $this->action->loadPublicCharacters(3);

    expect($result)->toHaveCount(3);
});
