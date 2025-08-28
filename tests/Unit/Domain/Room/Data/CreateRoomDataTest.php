<?php

declare(strict_types=1);
use Domain\Room\Data\CreateRoomData;
use Illuminate\Validation\ValidationException;
use Livewire\Wireable;
use PHPUnit\Framework\Attributes\Test;
it('implements wireable interface', function () {
    $createData = new CreateRoomData(
        name: 'Test Room',
        description: 'Test Description',
        password: 'password',
        guest_count: 3
    );

    expect($createData)->toBeInstanceOf(Wireable::class);
});
it('creates from valid array', function () {
    $data = [
        'name' => 'Epic Adventure Room',
        'description' => 'A room for epic adventures',
        'password' => 'secret123',
        'guest_count' => 4,
    ];

    $createData = CreateRoomData::from($data);

    expect($createData->name)->toEqual('Epic Adventure Room');
    expect($createData->description)->toEqual('A room for epic adventures');
    expect($createData->password)->toEqual('secret123');
    expect($createData->guest_count)->toEqual(4);
});
it('validates required name field', function () {
    expectException(ValidationException::class);

    CreateRoomData::validate([
        'description' => 'Missing name field',
        'password' => 'password',
        'guest_count' => 2,
    ]);
});
it('validates required description field', function () {
    expectException(ValidationException::class);

    CreateRoomData::validate([
        'name' => 'Test Room',
        'password' => 'password',
        'guest_count' => 2,
    ]);
});
it('allows optional password field', function () {
    // Password should be optional, so this should NOT throw an exception
    $data = CreateRoomData::from([
        'name' => 'Test Room',
        'description' => 'Test Description',
        'guest_count' => 2,
        'password' => null,
    ]);

    expect($data->password)->toBeNull();
    expect($data->name)->toEqual('Test Room');
    expect($data->description)->toEqual('Test Description');
    expect($data->guest_count)->toEqual(2);
});
it('validates required guest count field', function () {
    expectException(ValidationException::class);

    CreateRoomData::validate([
        'name' => 'Test Room',
        'description' => 'Test Description',
        'password' => 'password',
    ]);
});
it('validates name max length', function () {
    expectException(ValidationException::class);

    CreateRoomData::validate([
        'name' => str_repeat('A', 101), // Exceeds max length
        'description' => 'Valid description',
        'password' => 'password',
        'guest_count' => 3,
    ]);
});
it('validates description max length', function () {
    expectException(ValidationException::class);

    CreateRoomData::validate([
        'name' => 'Valid name',
        'description' => str_repeat('B', 501), // Exceeds max length
        'password' => 'password',
        'guest_count' => 3,
    ]);
});
it('validates password max length', function () {
    expectException(ValidationException::class);

    CreateRoomData::validate([
        'name' => 'Valid name',
        'description' => 'Valid description',
        'password' => str_repeat('P', 256), // Exceeds max length
        'guest_count' => 3,
    ]);
});
it('validates guest count minimum', function () {
    expectException(ValidationException::class);

    CreateRoomData::validate([
        'name' => 'Valid name',
        'description' => 'Valid description',
        'password' => 'password',
        'guest_count' => 1, // Below minimum
    ]);
});
it('validates guest count maximum', function () {
    expectException(ValidationException::class);

    CreateRoomData::validate([
        'name' => 'Valid name',
        'description' => 'Valid description',
        'password' => 'password',
        'guest_count' => 7, // Exceeds maximum
    ]);
});
it('accepts name at max length', function () {
    $data = [
        'name' => str_repeat('A', 100), // Exactly max length
        'description' => 'Valid description',
        'password' => 'password',
        'guest_count' => 3,
    ];

    $createData = CreateRoomData::from($data);

    expect($createData->name)->toEqual(str_repeat('A', 100));
});
it('accepts description at max length', function () {
    $data = [
        'name' => 'Valid name',
        'description' => str_repeat('B', 500), // Exactly max length
        'password' => 'password',
        'guest_count' => 3,
    ];

    $createData = CreateRoomData::from($data);

    expect($createData->description)->toEqual(str_repeat('B', 500));
});
it('accepts password at max length', function () {
    $data = [
        'name' => 'Valid name',
        'description' => 'Valid description',
        'password' => str_repeat('P', 255), // Exactly max length
        'guest_count' => 3,
    ];

    $createData = CreateRoomData::from($data);

    expect($createData->password)->toEqual(str_repeat('P', 255));
});
it('accepts all valid guest counts', function () {
    foreach ([2, 3, 4, 5, 6] as $guestCount) {
        $data = [
            'name' => 'Valid name',
            'description' => 'Valid description',
            'password' => 'password',
            'guest_count' => $guestCount,
        ];

        $createData = CreateRoomData::from($data);
        expect($createData->guest_count)->toEqual($guestCount);
    }
});
it('accepts minimal valid data', function () {
    $data = [
        'name' => 'A',
        'description' => 'B',
        'password' => 'p',
        'guest_count' => 2,
    ];

    $createData = CreateRoomData::from($data);

    expect($createData->name)->toEqual('A');
    expect($createData->description)->toEqual('B');
    expect($createData->password)->toEqual('p');
    expect($createData->guest_count)->toEqual(2);
});
it('handles whitespace in fields', function () {
    $data = [
        'name' => '  Test Room  ',
        'description' => '  Test Description  ',
        'password' => '  password  ',
        'guest_count' => 2,
    ];

    $createData = CreateRoomData::from($data);

    // Values should be preserved as-is (trimming is usually done at validation level)
    expect($createData->name)->toEqual('  Test Room  ');
    expect($createData->description)->toEqual('  Test Description  ');
    expect($createData->password)->toEqual('  password  ');
});
it('handles special characters', function () {
    $data = [
        'name' => 'Room with émojis 🎲 & symbols!',
        'description' => 'Description with special chars: @#$%^&*()',
        'password' => 'p@$$w0rd!',
        'guest_count' => 3,
    ];

    $createData = CreateRoomData::from($data);

    expect($createData->name)->toEqual('Room with émojis 🎲 & symbols!');
    expect($createData->description)->toEqual('Description with special chars: @#$%^&*()');
    expect($createData->password)->toEqual('p@$$w0rd!');
});
it('validates empty string as invalid', function () {
    expectException(ValidationException::class);

    CreateRoomData::validate([
        'name' => '',
        'description' => 'Valid description',
        'password' => 'password',
        'guest_count' => 2,
    ]);
});
it('validates null values as invalid', function () {
    expectException(ValidationException::class);

    CreateRoomData::validate([
        'name' => null,
        'description' => 'Valid description',
        'password' => 'password',
        'guest_count' => 2,
    ]);
});
it('works with livewire to livewire', function () {
    $createData = new CreateRoomData(
        name: 'Livewire Room',
        description: 'Testing Livewire',
        password: 'secret123',
        guest_count: 4
    );

    $livewireArray = $createData->toLivewire();
    $restoredData = CreateRoomData::fromLivewire($livewireArray);

    expect($restoredData->name)->toEqual($createData->name);
    expect($restoredData->description)->toEqual($createData->description);
    expect($restoredData->password)->toEqual($createData->password);
    expect($restoredData->guest_count)->toEqual($createData->guest_count);
});
it('works with livewire from livewire', function () {
    $originalData = [
        'name' => 'Original Room',
        'description' => 'Original Description',
        'password' => 'original123',
        'guest_count' => 2,
    ];

    $createData = CreateRoomData::from($originalData);
    $livewireArray = $createData->toLivewire();
    $restoredData = CreateRoomData::fromLivewire($livewireArray);

    expect($restoredData->name)->toEqual($createData->name);
    expect($restoredData->description)->toEqual($createData->description);
    expect($restoredData->password)->toEqual($createData->password);
    expect($restoredData->guest_count)->toEqual($createData->guest_count);
});
it('handles multiline descriptions', function () {
    $data = [
        'name' => 'Multiline Room',
        'description' => "Line 1\nLine 2\nLine 3",
        'password' => 'password',
        'guest_count' => 3,
    ];

    $createData = CreateRoomData::from($data);

    expect($createData->description)->toEqual("Line 1\nLine 2\nLine 3");
});
it('preserves unicode characters', function () {
    $data = [
        'name' => 'Room 中文 العربية русский',
        'description' => 'Unicode test: 🎲 ♠️ ♥️ ♦️ ♣️',
        'password' => 'pässwörd',
        'guest_count' => 2,
    ];

    $createData = CreateRoomData::from($data);

    expect($createData->name)->toEqual('Room 中文 العربية русский');
    expect($createData->description)->toEqual('Unicode test: 🎲 ♠️ ♥️ ♦️ ♣️');
    expect($createData->password)->toEqual('pässwörd');
});
it('handles numeric strings', function () {
    $data = [
        'name' => '123',
        'description' => '456',
        'password' => '789',
        'guest_count' => 1,
    ];

    $createData = CreateRoomData::from($data);

    expect($createData->name)->toEqual('123');
    expect($createData->description)->toEqual('456');
    expect($createData->password)->toEqual('789');
    expect($createData->guest_count)->toEqual(1);
});
it('handles extra fields gracefully', function () {
    $data = [
        'name' => 'Test Room',
        'description' => 'Test Description',
        'password' => 'password',
        'guest_count' => 2,
        'extra_field' => 'should be ignored',
        'another_field' => 123,
    ];

    $createData = CreateRoomData::from($data);

    expect($createData->name)->toEqual('Test Room');
    expect($createData->description)->toEqual('Test Description');
    expect($createData->password)->toEqual('password');
    expect($createData->guest_count)->toEqual(2);
    // Extra fields should not cause errors
});
