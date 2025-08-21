<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\CharacterBuilder;
use Domain\Character\Models\Character;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CharacterBuilderDomainCardTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function character_builder_allows_correct_number_of_domain_cards_for_school_of_knowledge(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->for($user)->create([
            'character_key' => 'TEST123',
            'class' => 'wizard',
            'subclass' => 'school of knowledge',
        ]);

        $component = Livewire::test(CharacterBuilder::class, ['characterKey' => 'TEST123']);

        // Set class and subclass
        $component->set('character.selected_class', 'wizard');
        $component->set('character.selected_subclass', 'school of knowledge');

        // Should allow selecting up to 5 cards for School of Knowledge
        $maxCards = $component->get('character')->getMaxDomainCards();
        $this->assertEquals(5, $maxCards);

        // Use real ability keys from abilities.json instead of fake ones
        // Try to select more than 2 cards (which would fail for regular subclasses)
        $component->call('selectDomainCard', 'codex', 'book of ava');
        $component->call('selectDomainCard', 'midnight', 'chokehold'); 
        $component->call('selectDomainCard', 'codex', 'book of illiat');

        // Should have selected 3 cards (more than the standard 2)
        $this->assertCount(3, $component->get('character.selected_domain_cards'));
    }

    #[Test]
    public function character_builder_allows_correct_number_of_domain_cards_for_regular_subclass(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->for($user)->create([
            'character_key' => 'TEST456',
            'class' => 'warrior',
            'subclass' => 'stalwart',
        ]);

        $component = Livewire::test(CharacterBuilder::class, ['characterKey' => 'TEST456']);

        // Set class and subclass
        $component->set('character.selected_class', 'warrior');
        $component->set('character.selected_subclass', 'stalwart');

        // Should allow selecting only 2 cards for regular subclass
        $maxCards = $component->get('character')->getMaxDomainCards();
        $this->assertEquals(2, $maxCards);

        // Simulate selecting cards up to the limit using real ability keys
        $component->call('selectDomainCard', 'blade', 'a soldiers bond');
        $component->call('selectDomainCard', 'bone', 'bare bones');

        // Should have selected the maximum number of cards
        $this->assertCount(2, $component->get('character.selected_domain_cards'));

        // Attempting to select another card should not increase the count
        $component->call('selectDomainCard', 'blade', 'battle monster');
        $this->assertCount(2, $component->get('character.selected_domain_cards'));
    }

    #[Test]
    public function character_builder_domain_card_deselection_works_correctly(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->for($user)->create([
            'character_key' => 'TEST789',
            'class' => 'wizard',
            'subclass' => 'school of knowledge',
        ]);

        $component = Livewire::test(CharacterBuilder::class, ['characterKey' => 'TEST789']);

        // Set class and subclass
        $component->set('character.selected_class', 'wizard');
        $component->set('character.selected_subclass', 'school of knowledge');

        // Select a card using a real ability key
        $component->call('selectDomainCard', 'codex', 'book of ava');

        // Should have 1 card
        $this->assertCount(1, $component->get('character.selected_domain_cards'));

        // Deselect the card by clicking it again
        $component->call('selectDomainCard', 'codex', 'book of ava');

        // Should now have 0 cards
        $this->assertCount(0, $component->get('character.selected_domain_cards'));
    }

    #[Test]
    public function character_builder_handles_null_subclass_domain_cards(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->for($user)->create([
            'character_key' => 'TEST000',
            'class' => 'warrior',
            'subclass' => null,
        ]);

        $component = Livewire::test(CharacterBuilder::class, ['characterKey' => 'TEST000']);

        // Set class and no subclass
        $component->set('character.selected_class', 'warrior');
        $component->set('character.selected_subclass', null);

        // Should allow selecting only 2 cards for null subclass
        $maxCards = $component->get('character')->getMaxDomainCards();
        $this->assertEquals(2, $maxCards);

        // Should be able to select up to 2 cards using real ability keys
        $component->call('selectDomainCard', 'blade', 'a soldiers bond');
        $component->call('selectDomainCard', 'bone', 'bare bones');

        $this->assertCount(2, $component->get('character.selected_domain_cards'));

        // Attempting to select a third card should not increase the count
        $component->call('selectDomainCard', 'blade', 'battle monster');
        $this->assertCount(2, $component->get('character.selected_domain_cards'));
    }
}
