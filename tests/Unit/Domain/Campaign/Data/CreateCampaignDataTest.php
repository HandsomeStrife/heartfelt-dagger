<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Campaign\Data;

use Domain\Campaign\Data\CreateCampaignData;
use Illuminate\Validation\ValidationException;
use Livewire\Wireable;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateCampaignDataTest extends TestCase
{
    #[Test]
    public function it_implements_wireable_interface(): void
    {
        $createData = new CreateCampaignData(
            name: 'Test Campaign',
            description: 'Test Description'
        );

        $this->assertInstanceOf(Wireable::class, $createData);
    }

    #[Test]
    public function it_creates_from_valid_array(): void
    {
        $data = [
            'name' => 'Campaign from Array',
            'description' => 'This campaign was created from an array',
        ];

        $createData = CreateCampaignData::from($data);

        $this->assertEquals('Campaign from Array', $createData->name);
        $this->assertEquals('This campaign was created from an array', $createData->description);
    }

    #[Test]
    public function it_validates_required_name_field(): void
    {
        $this->expectException(ValidationException::class);

        CreateCampaignData::validate([
            'description' => 'Missing name field',
        ]);
    }

    #[Test]
    public function it_validates_required_description_field(): void
    {
        $this->expectException(ValidationException::class);

        CreateCampaignData::validate([
            'name' => 'Campaign Name',
        ]);
    }

    #[Test]
    public function it_validates_name_max_length(): void
    {
        $this->expectException(ValidationException::class);

        CreateCampaignData::validate([
            'name' => str_repeat('A', 101), // Exceeds 100 character limit
            'description' => 'Valid description',
        ]);
    }

    #[Test]
    public function it_validates_description_max_length(): void
    {
        $this->expectException(ValidationException::class);

        CreateCampaignData::validate([
            'name' => 'Valid Name',
            'description' => str_repeat('B', 1001), // Exceeds 1000 character limit
        ]);
    }

    #[Test]
    public function it_accepts_name_at_max_length(): void
    {
        $maxLengthName = str_repeat('A', 100); // Exactly 100 characters

        $createData = CreateCampaignData::from([
            'name' => $maxLengthName,
            'description' => 'Valid description',
        ]);

        $this->assertEquals($maxLengthName, $createData->name);
        $this->assertEquals(100, strlen($createData->name));
    }

    #[Test]
    public function it_accepts_description_at_max_length(): void
    {
        $maxLengthDescription = str_repeat('B', 1000); // Exactly 1000 characters

        $createData = CreateCampaignData::from([
            'name' => 'Valid Name',
            'description' => $maxLengthDescription,
        ]);

        $this->assertEquals($maxLengthDescription, $createData->description);
        $this->assertEquals(1000, strlen($createData->description));
    }

    #[Test]
    public function it_accepts_minimal_valid_data(): void
    {
        $createData = CreateCampaignData::from([
            'name' => 'A',
            'description' => 'B',
        ]);

        $this->assertEquals('A', $createData->name);
        $this->assertEquals('B', $createData->description);
    }

    #[Test]
    public function it_handles_whitespace_in_fields(): void
    {
        $createData = CreateCampaignData::from([
            'name' => '  Whitespace Campaign  ',
            'description' => '  Description with spaces  ',
        ]);

        $this->assertEquals('  Whitespace Campaign  ', $createData->name);
        $this->assertEquals('  Description with spaces  ', $createData->description);
    }

    #[Test]
    public function it_handles_special_characters(): void
    {
        $createData = CreateCampaignData::from([
            'name' => 'Campaign with "Quotes" & Symbols!',
            'description' => 'Description with àccénts, émojis 🎲, and other symbols: @#$%^&*()',
        ]);

        $this->assertEquals('Campaign with "Quotes" & Symbols!', $createData->name);
        $this->assertEquals('Description with àccénts, émojis 🎲, and other symbols: @#$%^&*()', $createData->description);
    }

    #[Test]
    public function it_validates_empty_string_as_invalid(): void
    {
        $this->expectException(ValidationException::class);

        CreateCampaignData::validate([
            'name' => '',
            'description' => 'Valid description',
        ]);
    }

    #[Test]
    public function it_validates_null_values_as_invalid(): void
    {
        $this->expectException(ValidationException::class);

        CreateCampaignData::validate([
            'name' => null,
            'description' => 'Valid description',
        ]);
    }

    #[Test]
    public function it_works_with_livewire_toLivewire(): void
    {
        $createData = new CreateCampaignData(
            name: 'Livewire Campaign',
            description: 'Testing Livewire compatibility'
        );

        $livewireData = $createData->toLivewire();

        $this->assertIsArray($livewireData);
        $this->assertArrayHasKey('name', $livewireData);
        $this->assertArrayHasKey('description', $livewireData);
        $this->assertEquals('Livewire Campaign', $livewireData['name']);
        $this->assertEquals('Testing Livewire compatibility', $livewireData['description']);
    }

    #[Test]
    public function it_works_with_livewire_fromLivewire(): void
    {
        $livewireData = [
            'name' => 'From Livewire Campaign',
            'description' => 'Created from Livewire data',
        ];

        $createData = CreateCampaignData::fromLivewire($livewireData);

        $this->assertInstanceOf(CreateCampaignData::class, $createData);
        $this->assertEquals('From Livewire Campaign', $createData->name);
        $this->assertEquals('Created from Livewire data', $createData->description);
    }

    #[Test]
    public function it_handles_multiline_descriptions(): void
    {
        $multilineDescription = "This is a campaign description\nwith multiple lines\nand various content.";

        $createData = CreateCampaignData::from([
            'name' => 'Multiline Campaign',
            'description' => $multilineDescription,
        ]);

        $this->assertEquals($multilineDescription, $createData->description);
        $this->assertStringContainsString("\n", $createData->description);
    }

    #[Test]
    public function it_preserves_unicode_characters(): void
    {
        $createData = CreateCampaignData::from([
            'name' => 'Ëlvës & Drâgøns',
            'description' => 'A fantasy campaign with ünïcødë characters: ♠♥♦♣',
        ]);

        $this->assertEquals('Ëlvës & Drâgøns', $createData->name);
        $this->assertEquals('A fantasy campaign with ünïcødë characters: ♠♥♦♣', $createData->description);
    }

    #[Test]
    public function it_handles_numeric_strings(): void
    {
        $createData = CreateCampaignData::from([
            'name' => '123 Campaign',
            'description' => 'Campaign number 456',
        ]);

        $this->assertEquals('123 Campaign', $createData->name);
        $this->assertEquals('Campaign number 456', $createData->description);
    }

    #[Test]
    public function it_rejects_non_string_types(): void
    {
        $this->expectException(ValidationException::class);

        CreateCampaignData::validate([
            'name' => 123, // Integer instead of string
            'description' => 'Valid description',
        ]);
    }

    #[Test]
    public function it_handles_extra_fields_gracefully(): void
    {
        $createData = CreateCampaignData::from([
            'name' => 'Extra Fields Campaign',
            'description' => 'Valid description',
            'extra_field' => 'This should be ignored',
            'another_field' => 123,
        ]);

        $this->assertEquals('Extra Fields Campaign', $createData->name);
        $this->assertEquals('Valid description', $createData->description);
        // Extra fields should not be accessible on the data object
    }
}
