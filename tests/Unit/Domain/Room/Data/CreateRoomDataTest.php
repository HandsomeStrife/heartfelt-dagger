<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Room\Data;

use Domain\Room\Data\CreateRoomData;
use Illuminate\Validation\ValidationException;
use Livewire\Wireable;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateRoomDataTest extends TestCase
{
    #[Test]
    public function it_implements_wireable_interface(): void
    {
        $createData = new CreateRoomData(
            name: 'Test Room',
            description: 'Test Description',
            password: 'password',
            guest_count: 3
        );

        $this->assertInstanceOf(Wireable::class, $createData);
    }

    #[Test]
    public function it_creates_from_valid_array(): void
    {
        $data = [
            'name' => 'Epic Adventure Room',
            'description' => 'A room for epic adventures',
            'password' => 'secret123',
            'guest_count' => 4,
        ];

        $createData = CreateRoomData::from($data);

        $this->assertEquals('Epic Adventure Room', $createData->name);
        $this->assertEquals('A room for epic adventures', $createData->description);
        $this->assertEquals('secret123', $createData->password);
        $this->assertEquals(4, $createData->guest_count);
    }

    #[Test]
    public function it_validates_required_name_field(): void
    {
        $this->expectException(ValidationException::class);

        CreateRoomData::validate([
            'description' => 'Missing name field',
            'password' => 'password',
            'guest_count' => 2,
        ]);
    }

    #[Test]
    public function it_validates_required_description_field(): void
    {
        $this->expectException(ValidationException::class);

        CreateRoomData::validate([
            'name' => 'Test Room',
            'password' => 'password',
            'guest_count' => 2,
        ]);
    }

    #[Test]
    public function it_validates_required_password_field(): void
    {
        $this->expectException(ValidationException::class);

        CreateRoomData::validate([
            'name' => 'Test Room',
            'description' => 'Test Description',
            'guest_count' => 2,
        ]);
    }

    #[Test]
    public function it_validates_required_guest_count_field(): void
    {
        $this->expectException(ValidationException::class);

        CreateRoomData::validate([
            'name' => 'Test Room',
            'description' => 'Test Description',
            'password' => 'password',
        ]);
    }

    #[Test]
    public function it_validates_name_max_length(): void
    {
        $this->expectException(ValidationException::class);

        CreateRoomData::validate([
            'name' => str_repeat('A', 101), // Exceeds max length
            'description' => 'Valid description',
            'password' => 'password',
            'guest_count' => 3,
        ]);
    }

    #[Test]
    public function it_validates_description_max_length(): void
    {
        $this->expectException(ValidationException::class);

        CreateRoomData::validate([
            'name' => 'Valid name',
            'description' => str_repeat('B', 501), // Exceeds max length
            'password' => 'password',
            'guest_count' => 3,
        ]);
    }

    #[Test]
    public function it_validates_password_max_length(): void
    {
        $this->expectException(ValidationException::class);

        CreateRoomData::validate([
            'name' => 'Valid name',
            'description' => 'Valid description',
            'password' => str_repeat('P', 256), // Exceeds max length
            'guest_count' => 3,
        ]);
    }

    #[Test]
    public function it_validates_guest_count_minimum(): void
    {
        $this->expectException(ValidationException::class);

        CreateRoomData::validate([
            'name' => 'Valid name',
            'description' => 'Valid description',
            'password' => 'password',
            'guest_count' => 0, // Below minimum
        ]);
    }

    #[Test]
    public function it_validates_guest_count_maximum(): void
    {
        $this->expectException(ValidationException::class);

        CreateRoomData::validate([
            'name' => 'Valid name',
            'description' => 'Valid description',
            'password' => 'password',
            'guest_count' => 6, // Exceeds maximum
        ]);
    }

    #[Test]
    public function it_accepts_name_at_max_length(): void
    {
        $data = [
            'name' => str_repeat('A', 100), // Exactly max length
            'description' => 'Valid description',
            'password' => 'password',
            'guest_count' => 3,
        ];

        $createData = CreateRoomData::from($data);

        $this->assertEquals(str_repeat('A', 100), $createData->name);
    }

    #[Test]
    public function it_accepts_description_at_max_length(): void
    {
        $data = [
            'name' => 'Valid name',
            'description' => str_repeat('B', 500), // Exactly max length
            'password' => 'password',
            'guest_count' => 3,
        ];

        $createData = CreateRoomData::from($data);

        $this->assertEquals(str_repeat('B', 500), $createData->description);
    }

    #[Test]
    public function it_accepts_password_at_max_length(): void
    {
        $data = [
            'name' => 'Valid name',
            'description' => 'Valid description',
            'password' => str_repeat('P', 255), // Exactly max length
            'guest_count' => 3,
        ];

        $createData = CreateRoomData::from($data);

        $this->assertEquals(str_repeat('P', 255), $createData->password);
    }

    #[Test]
    public function it_accepts_all_valid_guest_counts(): void
    {
        foreach ([1, 2, 3, 4, 5] as $guestCount) {
            $data = [
                'name' => 'Valid name',
                'description' => 'Valid description',
                'password' => 'password',
                'guest_count' => $guestCount,
            ];

            $createData = CreateRoomData::from($data);
            $this->assertEquals($guestCount, $createData->guest_count);
        }
    }

    #[Test]
    public function it_accepts_minimal_valid_data(): void
    {
        $data = [
            'name' => 'A',
            'description' => 'B',
            'password' => 'p',
            'guest_count' => 1,
        ];

        $createData = CreateRoomData::from($data);

        $this->assertEquals('A', $createData->name);
        $this->assertEquals('B', $createData->description);
        $this->assertEquals('p', $createData->password);
        $this->assertEquals(1, $createData->guest_count);
    }

    #[Test]
    public function it_handles_whitespace_in_fields(): void
    {
        $data = [
            'name' => '  Test Room  ',
            'description' => '  Test Description  ',
            'password' => '  password  ',
            'guest_count' => 2,
        ];

        $createData = CreateRoomData::from($data);

        // Values should be preserved as-is (trimming is usually done at validation level)
        $this->assertEquals('  Test Room  ', $createData->name);
        $this->assertEquals('  Test Description  ', $createData->description);
        $this->assertEquals('  password  ', $createData->password);
    }

    #[Test]
    public function it_handles_special_characters(): void
    {
        $data = [
            'name' => 'Room with émojis 🎲 & symbols!',
            'description' => 'Description with special chars: @#$%^&*()',
            'password' => 'p@$$w0rd!',
            'guest_count' => 3,
        ];

        $createData = CreateRoomData::from($data);

        $this->assertEquals('Room with émojis 🎲 & symbols!', $createData->name);
        $this->assertEquals('Description with special chars: @#$%^&*()', $createData->description);
        $this->assertEquals('p@$$w0rd!', $createData->password);
    }

    #[Test]
    public function it_validates_empty_string_as_invalid(): void
    {
        $this->expectException(ValidationException::class);

        CreateRoomData::validate([
            'name' => '',
            'description' => 'Valid description',
            'password' => 'password',
            'guest_count' => 2,
        ]);
    }

    #[Test]
    public function it_validates_null_values_as_invalid(): void
    {
        $this->expectException(ValidationException::class);

        CreateRoomData::validate([
            'name' => null,
            'description' => 'Valid description',
            'password' => 'password',
            'guest_count' => 2,
        ]);
    }

    #[Test]
    public function it_works_with_livewire_to_livewire(): void
    {
        $createData = new CreateRoomData(
            name: 'Livewire Room',
            description: 'Testing Livewire',
            password: 'secret123',
            guest_count: 4
        );

        $livewireArray = $createData->toLivewire();
        $restoredData = CreateRoomData::fromLivewire($livewireArray);

        $this->assertEquals($createData->name, $restoredData->name);
        $this->assertEquals($createData->description, $restoredData->description);
        $this->assertEquals($createData->password, $restoredData->password);
        $this->assertEquals($createData->guest_count, $restoredData->guest_count);
    }

    #[Test]
    public function it_works_with_livewire_from_livewire(): void
    {
        $originalData = [
            'name' => 'Original Room',
            'description' => 'Original Description',
            'password' => 'original123',
            'guest_count' => 2,
        ];

        $createData = CreateRoomData::from($originalData);
        $livewireArray = $createData->toLivewire();
        $restoredData = CreateRoomData::fromLivewire($livewireArray);

        $this->assertEquals($createData->name, $restoredData->name);
        $this->assertEquals($createData->description, $restoredData->description);
        $this->assertEquals($createData->password, $restoredData->password);
        $this->assertEquals($createData->guest_count, $restoredData->guest_count);
    }

    #[Test]
    public function it_handles_multiline_descriptions(): void
    {
        $data = [
            'name' => 'Multiline Room',
            'description' => "Line 1\nLine 2\nLine 3",
            'password' => 'password',
            'guest_count' => 3,
        ];

        $createData = CreateRoomData::from($data);

        $this->assertEquals("Line 1\nLine 2\nLine 3", $createData->description);
    }

    #[Test]
    public function it_preserves_unicode_characters(): void
    {
        $data = [
            'name' => 'Room 中文 العربية русский',
            'description' => 'Unicode test: 🎲 ♠️ ♥️ ♦️ ♣️',
            'password' => 'pässwörd',
            'guest_count' => 2,
        ];

        $createData = CreateRoomData::from($data);

        $this->assertEquals('Room 中文 العربية русский', $createData->name);
        $this->assertEquals('Unicode test: 🎲 ♠️ ♥️ ♦️ ♣️', $createData->description);
        $this->assertEquals('pässwörd', $createData->password);
    }

    #[Test]
    public function it_handles_numeric_strings(): void
    {
        $data = [
            'name' => '123',
            'description' => '456',
            'password' => '789',
            'guest_count' => 1,
        ];

        $createData = CreateRoomData::from($data);

        $this->assertEquals('123', $createData->name);
        $this->assertEquals('456', $createData->description);
        $this->assertEquals('789', $createData->password);
        $this->assertEquals(1, $createData->guest_count);
    }

    #[Test]
    public function it_handles_extra_fields_gracefully(): void
    {
        $data = [
            'name' => 'Test Room',
            'description' => 'Test Description',
            'password' => 'password',
            'guest_count' => 2,
            'extra_field' => 'should be ignored',
            'another_field' => 123,
        ];

        $createData = CreateRoomData::from($data);

        $this->assertEquals('Test Room', $createData->name);
        $this->assertEquals('Test Description', $createData->description);
        $this->assertEquals('password', $createData->password);
        $this->assertEquals(2, $createData->guest_count);
        // Extra fields should not cause errors
    }
}
