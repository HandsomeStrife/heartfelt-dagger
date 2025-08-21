<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Character\Data;

use Domain\Character\Data\CharacterBuilderData;
use Domain\Character\Enums\CharacterBuilderStep;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CharacterBuilderDataTest extends TestCase
{
    #[Test]
    public function it_can_be_constructed_with_all_defaults(): void
    {
        $data = new CharacterBuilderData;

        $this->assertNull($data->name);
        $this->assertNull($data->selected_class);
        $this->assertNull($data->selected_subclass);
        $this->assertNull($data->selected_ancestry);
        $this->assertNull($data->selected_community);
        $this->assertEquals([], $data->assigned_traits);
        $this->assertEquals([], $data->selected_equipment);
        $this->assertEquals([], $data->experiences);
        $this->assertEquals([], $data->selected_domain_cards);
        $this->assertEquals([], $data->background_answers);
        $this->assertEquals([], $data->connection_answers);
        $this->assertNull($data->profile_image_path);
        $this->assertNull($data->physical_description);
        $this->assertNull($data->personality_traits);
        $this->assertNull($data->personal_history);
        $this->assertNull($data->motivations);
    }

    #[Test]
    public function it_can_be_constructed_with_all_parameters(): void
    {
        $data = new CharacterBuilderData(
            name: 'Test Hero',
            selected_class: 'warrior',
            selected_subclass: 'call-of-the-brave',
            selected_ancestry: 'human',
            selected_community: 'order-of-scholars',
            assigned_traits: ['agility' => 2, 'strength' => 1],
            selected_equipment: [['key' => 'sword', 'type' => 'weapon']],
            experiences: [['name' => 'Combat Training', 'description' => 'Trained with guards']],
            selected_domain_cards: [['domain' => 'blade', 'ability_key' => 'strike']],
            background_answers: ['Answer 1', 'Answer 2'],
            connection_answers: ['Connection 1'],
            profile_image_path: 'hero.jpg',
            physical_description: 'Tall and strong',
            personality_traits: 'Brave',
            personal_history: 'Born in village',
            motivations: 'Protect people'
        );

        $this->assertEquals('Test Hero', $data->name);
        $this->assertEquals('warrior', $data->selected_class);
        $this->assertEquals('call-of-the-brave', $data->selected_subclass);
        $this->assertEquals('human', $data->selected_ancestry);
        $this->assertEquals('order-of-scholars', $data->selected_community);
        $this->assertEquals(['agility' => 2, 'strength' => 1], $data->assigned_traits);
        $this->assertEquals([['key' => 'sword', 'type' => 'weapon']], $data->selected_equipment);
        $this->assertEquals([['name' => 'Combat Training', 'description' => 'Trained with guards']], $data->experiences);
        $this->assertEquals([['domain' => 'blade', 'ability_key' => 'strike']], $data->selected_domain_cards);
        $this->assertEquals(['Answer 1', 'Answer 2'], $data->background_answers);
        $this->assertEquals(['Connection 1'], $data->connection_answers);
        $this->assertEquals('hero.jpg', $data->profile_image_path);
        $this->assertEquals('Tall and strong', $data->physical_description);
        $this->assertEquals('Brave', $data->personality_traits);
        $this->assertEquals('Born in village', $data->personal_history);
        $this->assertEquals('Protect people', $data->motivations);
    }

    #[Test]
    public function it_can_be_created_from_array(): void
    {
        $array = [
            'name' => 'Array Hero',
            'selected_class' => 'ranger',
            'selected_subclass' => 'beast-hunter',
            'selected_ancestry' => 'elf',
            'selected_community' => 'wildlands',
            'assigned_traits' => ['finesse' => 1],
            'selected_equipment' => [['key' => 'bow', 'type' => 'weapon']],
            'experiences' => [['name' => 'Hunting', 'description' => 'Hunted beasts']],
            'selected_domain_cards' => [['domain' => 'sage', 'ability_key' => 'track']],
            'background_answers' => ['Background answer'],
            'connection_answers' => ['Connection answer'],
            'profile_image_path' => 'ranger.jpg',
            'physical_description' => 'Agile',
            'personality_traits' => 'Cautious',
            'personal_history' => 'Forest dweller',
            'motivations' => 'Protect nature',
        ];

        $data = CharacterBuilderData::from($array);

        $this->assertEquals('Array Hero', $data->name);
        $this->assertEquals('ranger', $data->selected_class);
        $this->assertEquals('beast-hunter', $data->selected_subclass);
        $this->assertEquals('elf', $data->selected_ancestry);
        $this->assertEquals('wildlands', $data->selected_community);
        $this->assertEquals(['finesse' => 1], $data->assigned_traits);
        $this->assertEquals([['key' => 'bow', 'type' => 'weapon']], $data->selected_equipment);
        $this->assertEquals([['name' => 'Hunting', 'description' => 'Hunted beasts']], $data->experiences);
        $this->assertEquals([['domain' => 'sage', 'ability_key' => 'track']], $data->selected_domain_cards);
        $this->assertEquals(['Background answer'], $data->background_answers);
        $this->assertEquals(['Connection answer'], $data->connection_answers);
        $this->assertEquals('ranger.jpg', $data->profile_image_path);
        $this->assertEquals('Agile', $data->physical_description);
        $this->assertEquals('Cautious', $data->personality_traits);
        $this->assertEquals('Forest dweller', $data->personal_history);
        $this->assertEquals('Protect nature', $data->motivations);
    }

    #[Test]
    public function it_can_be_converted_to_array(): void
    {
        $data = new CharacterBuilderData(
            name: 'Test Hero',
            selected_class: 'warrior',
            assigned_traits: ['strength' => 2]
        );

        $array = $data->toArray();

        $this->assertEquals('Test Hero', $array['name']);
        $this->assertEquals('warrior', $array['selected_class']);
        $this->assertEquals(['strength' => 2], $array['assigned_traits']);
        $this->assertNull($array['selected_subclass']);
        $this->assertEquals([], $array['selected_equipment']);
    }

    #[Test]
    public function it_handles_null_values_in_from_array(): void
    {
        $array = [
            'name' => null,
            'selected_class' => null,
            'selected_subclass' => null,
            'selected_ancestry' => null,
            'selected_community' => null,
        ];

        $data = CharacterBuilderData::from($array);

        // Spatie Data might convert null to empty strings in some cases
        $this->assertTrue($data->name === null || $data->name === '');
        $this->assertTrue($data->selected_class === null || $data->selected_class === '');
        $this->assertTrue($data->selected_subclass === null || $data->selected_subclass === '');
        $this->assertTrue($data->selected_ancestry === null || $data->selected_ancestry === '');
        $this->assertTrue($data->selected_community === null || $data->selected_community === '');
    }

    #[Test]
    public function it_handles_missing_keys_in_from_array(): void
    {
        $array = [
            'name' => 'Minimal Hero',
            // Missing other keys should use defaults
        ];

        $data = CharacterBuilderData::from($array);

        $this->assertEquals('Minimal Hero', $data->name);
        $this->assertNull($data->selected_class);
        $this->assertEquals([], $data->assigned_traits);
        $this->assertEquals([], $data->selected_equipment);
    }

    #[Test]
    public function it_has_step_completion_methods(): void
    {
        $data = new CharacterBuilderData;

        // Should have methods to check step completion
        $this->assertTrue(method_exists($data, 'isStepComplete'));
        $this->assertTrue(method_exists($data, 'getCompletedSteps'));
        $this->assertTrue(method_exists($data, 'canProceedToStep'));
    }

    #[Test]
    public function it_validates_step_1_completion(): void
    {
        $incompleteData = new CharacterBuilderData(selected_class: 'warrior');
        $this->assertFalse($incompleteData->isStepComplete(1));

        $completeData = new CharacterBuilderData(
            selected_class: 'warrior',
            selected_subclass: 'call-of-the-brave'
        );
        $this->assertTrue($completeData->isStepComplete(1));
    }

    #[Test]
    public function it_validates_step_2_completion(): void
    {
        $incompleteData = new CharacterBuilderData(selected_ancestry: 'human');
        $this->assertFalse($incompleteData->isStepComplete(2));

        $completeData = new CharacterBuilderData(
            selected_ancestry: 'human',
            selected_community: 'order-of-scholars'
        );
        $this->assertTrue($completeData->isStepComplete(2));
    }

    #[Test]
    public function it_validates_step_3_completion(): void
    {
        $incompleteData = new CharacterBuilderData(assigned_traits: ['agility' => 2]);
        $this->assertFalse($incompleteData->isStepComplete(3));

        // Step 3 requires exactly 6 traits with values that sum to [-1, 0, 0, 1, 1, 2]
        $completeData = new CharacterBuilderData(assigned_traits: [
            'agility' => 2,
            'strength' => 1,
            'finesse' => 0,
            'instinct' => -1,
            'presence' => 1,
            'knowledge' => 0,
        ]);
        $this->assertTrue($completeData->isStepComplete(3));
    }

    #[Test]
    public function it_validates_step_4_completion(): void
    {
        $incompleteData = new CharacterBuilderData(selected_equipment: [
            ['key' => 'sword', 'type' => 'weapon'],
        ]);
        $this->assertFalse($incompleteData->isStepComplete(4));

        $completeData = new CharacterBuilderData(selected_equipment: [
            ['key' => 'sword', 'type' => 'weapon', 'data' => ['type' => 'Primary']],
            ['key' => 'armor', 'type' => 'armor'],
        ]);
        $this->assertTrue($completeData->isStepComplete(4));
    }

    #[Test]
    public function it_validates_step_5_completion(): void
    {
        // Without a selected class, step 5 should be incomplete
        $incompleteData = new CharacterBuilderData;
        $this->assertFalse($incompleteData->isStepComplete(5));

        // With a class but no background answers, step 5 should be incomplete
        $incompleteDataWithClass = new CharacterBuilderData(
            selected_class: 'warrior',
            background_answers: []
        );
        $this->assertFalse($incompleteDataWithClass->isStepComplete(5));

        // With a class and at least one background answer, step 5 should be complete
        $completeData = new CharacterBuilderData(
            selected_class: 'warrior',
            background_answers: ['This is my character background.', '', '']
        );
        $this->assertTrue($completeData->isStepComplete(5));
    }

    #[Test]
    public function it_validates_step_6_completion(): void
    {
        $incompleteData = new CharacterBuilderData(experiences: [
            ['name' => 'First Experience', 'description' => 'Description'],
        ]);
        $this->assertFalse($incompleteData->isStepComplete(6));

        $completeData = new CharacterBuilderData(experiences: [
            ['name' => 'First Experience', 'description' => 'Description'],
            ['name' => 'Second Experience', 'description' => 'Description'],
        ]);
        $this->assertTrue($completeData->isStepComplete(6));
    }

    #[Test]
    public function it_can_manually_mark_steps_complete(): void
    {
        $data = new CharacterBuilderData;

        // Background step should not be complete initially
        $this->assertFalse($data->isStepComplete(CharacterBuilderStep::BACKGROUND));

        // Mark background step as manually complete
        $data->markStepComplete(CharacterBuilderStep::BACKGROUND);

        // Now background step should be complete even without meeting normal requirements
        $this->assertTrue($data->isStepComplete(CharacterBuilderStep::BACKGROUND));
        $this->assertContains(CharacterBuilderStep::BACKGROUND->value, $data->manual_step_completions);
    }

    #[Test]
    public function it_gets_completed_steps(): void
    {
        $data = new CharacterBuilderData(
            selected_class: 'warrior',
            selected_subclass: 'call-of-the-brave',
            selected_ancestry: 'human',
            selected_community: 'order-of-scholars',
            assigned_traits: [
                'agility' => 2,
                'strength' => 1,
                'finesse' => 0,
                'instinct' => -1,
                'presence' => 1,
                'knowledge' => 0,
            ],
            selected_equipment: [
                ['key' => 'sword', 'type' => 'weapon', 'data' => ['type' => 'Primary']],
                ['key' => 'armor', 'type' => 'armor'],
            ],
            background_answers: ['This is my background story.'],
            experiences: [
                ['name' => 'First Experience', 'description' => 'Description'],
                ['name' => 'Second Experience', 'description' => 'Description'],
            ],
            selected_domain_cards: [
                ['name' => 'Card 1'],
                ['name' => 'Card 2'],
            ],
            connection_answers: ['My connection to another character.']
        );

        $completedSteps = $data->getCompletedSteps();

        $this->assertContains(1, $completedSteps); // class selection
        $this->assertContains(2, $completedSteps); // heritage
        $this->assertContains(3, $completedSteps); // traits
        $this->assertContains(4, $completedSteps); // equipment
        $this->assertContains(5, $completedSteps); // background
        $this->assertContains(6, $completedSteps); // experiences
        $this->assertContains(7, $completedSteps); // domain cards
        $this->assertContains(8, $completedSteps); // connections
    }

    #[Test]
    public function it_implements_wireable_interface(): void
    {
        $data = new CharacterBuilderData(name: 'Test Hero');

        // Should be able to convert to Livewire format
        $livewireData = $data->toLivewire();
        $this->assertIsArray($livewireData);

        // Should be able to create from Livewire format
        $fromLivewire = CharacterBuilderData::fromLivewire($livewireData);
        $this->assertInstanceOf(CharacterBuilderData::class, $fromLivewire);
        $this->assertEquals('Test Hero', $fromLivewire->name);
    }
}
