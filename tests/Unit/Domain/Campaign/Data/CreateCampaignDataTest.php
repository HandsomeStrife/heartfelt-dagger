<?php

declare(strict_types=1);
use Domain\Campaign\Data\CreateCampaignData;
use Illuminate\Validation\ValidationException;
use Livewire\Wireable;
use PHPUnit\Framework\Attributes\Test;
it('implements wireable interface', function () {
    $createData = new CreateCampaignData(
        name: 'Test Campaign',
        description: 'Test Description'
    );

    expect($createData)->toBeInstanceOf(Wireable::class);
});
it('creates from valid array', function () {
    $data = [
        'name' => 'Campaign from Array',
        'description' => 'This campaign was created from an array',
    ];

    $createData = CreateCampaignData::from($data);

    expect($createData->name)->toEqual('Campaign from Array');
    expect($createData->description)->toEqual('This campaign was created from an array');
});
it('validates required name field', function () {
    $this->expectException(ValidationException::class);

    CreateCampaignData::validate([
        'description' => 'Missing name field',
    ]);
});
it('validates required description field', function () {
    $this->expectException(ValidationException::class);

    CreateCampaignData::validate([
        'name' => 'Campaign Name',
    ]);
});
it('validates name max length', function () {
    $this->expectException(ValidationException::class);

    CreateCampaignData::validate([
        'name' => str_repeat('A', 101), // Exceeds 100 character limit
        'description' => 'Valid description',
    ]);
});
it('validates description max length', function () {
    $this->expectException(ValidationException::class);

    CreateCampaignData::validate([
        'name' => 'Valid Name',
        'description' => str_repeat('B', 1001), // Exceeds 1000 character limit
    ]);
});
it('accepts name at max length', function () {
    $maxLengthName = str_repeat('A', 100);

    // Exactly 100 characters
    $createData = CreateCampaignData::from([
        'name' => $maxLengthName,
        'description' => 'Valid description',
    ]);

    expect($createData->name)->toEqual($maxLengthName);
    expect(strlen($createData->name))->toEqual(100);
});
it('accepts description at max length', function () {
    $maxLengthDescription = str_repeat('B', 1000);

    // Exactly 1000 characters
    $createData = CreateCampaignData::from([
        'name' => 'Valid Name',
        'description' => $maxLengthDescription,
    ]);

    expect($createData->description)->toEqual($maxLengthDescription);
    expect(strlen($createData->description))->toEqual(1000);
});
it('accepts minimal valid data', function () {
    $createData = CreateCampaignData::from([
        'name' => 'A',
        'description' => 'B',
    ]);

    expect($createData->name)->toEqual('A');
    expect($createData->description)->toEqual('B');
});
it('handles whitespace in fields', function () {
    $createData = CreateCampaignData::from([
        'name' => '  Whitespace Campaign  ',
        'description' => '  Description with spaces  ',
    ]);

    expect($createData->name)->toEqual('  Whitespace Campaign  ');
    expect($createData->description)->toEqual('  Description with spaces  ');
});
it('handles special characters', function () {
    $createData = CreateCampaignData::from([
        'name' => 'Campaign with "Quotes" & Symbols!',
        'description' => 'Description with àccénts, émojis 🎲, and other symbols: @#$%^&*()',
    ]);

    expect($createData->name)->toEqual('Campaign with "Quotes" & Symbols!');
    expect($createData->description)->toEqual('Description with àccénts, émojis 🎲, and other symbols: @#$%^&*()');
});
it('validates empty string as invalid', function () {
    $this->expectException(ValidationException::class);

    CreateCampaignData::validate([
        'name' => '',
        'description' => 'Valid description',
    ]);
});
it('validates null values as invalid', function () {
    $this->expectException(ValidationException::class);

    CreateCampaignData::validate([
        'name' => null,
        'description' => 'Valid description',
    ]);
});
it('works with livewire to livewire', function () {
    $createData = new CreateCampaignData(
        name: 'Livewire Campaign',
        description: 'Testing Livewire compatibility'
    );

    $livewireData = $createData->toLivewire();

    expect($livewireData)->toBeArray();
    expect($livewireData)->toHaveKey('name');
    expect($livewireData)->toHaveKey('description');
    expect($livewireData['name'])->toEqual('Livewire Campaign');
    expect($livewireData['description'])->toEqual('Testing Livewire compatibility');
});
it('works with livewire from livewire', function () {
    $livewireData = [
        'name' => 'From Livewire Campaign',
        'description' => 'Created from Livewire data',
    ];

    $createData = CreateCampaignData::fromLivewire($livewireData);

    expect($createData)->toBeInstanceOf(CreateCampaignData::class);
    expect($createData->name)->toEqual('From Livewire Campaign');
    expect($createData->description)->toEqual('Created from Livewire data');
});
it('handles multiline descriptions', function () {
    $multilineDescription = "This is a campaign description\nwith multiple lines\nand various content.";

    $createData = CreateCampaignData::from([
        'name' => 'Multiline Campaign',
        'description' => $multilineDescription,
    ]);

    expect($createData->description)->toEqual($multilineDescription);
    $this->assertStringContainsString("\n", $createData->description);
});
it('preserves unicode characters', function () {
    $createData = CreateCampaignData::from([
        'name' => 'Ëlvës & Drâgøns',
        'description' => 'A fantasy campaign with ünïcødë characters: ♠♥♦♣',
    ]);

    expect($createData->name)->toEqual('Ëlvës & Drâgøns');
    expect($createData->description)->toEqual('A fantasy campaign with ünïcødë characters: ♠♥♦♣');
});
it('handles numeric strings', function () {
    $createData = CreateCampaignData::from([
        'name' => '123 Campaign',
        'description' => 'Campaign number 456',
    ]);

    expect($createData->name)->toEqual('123 Campaign');
    expect($createData->description)->toEqual('Campaign number 456');
});
it('rejects non string types', function () {
    $this->expectException(ValidationException::class);

    CreateCampaignData::validate([
        'name' => 123, // Integer instead of string
        'description' => 'Valid description',
    ]);
});
it('handles extra fields gracefully', function () {
    $createData = CreateCampaignData::from([
        'name' => 'Extra Fields Campaign',
        'description' => 'Valid description',
        'extra_field' => 'This should be ignored',
        'another_field' => 123,
    ]);

    expect($createData->name)->toEqual('Extra Fields Campaign');
    expect($createData->description)->toEqual('Valid description');
    // Extra fields should not be accessible on the data object
});
