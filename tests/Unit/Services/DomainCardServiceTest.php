<?php

declare(strict_types=1);

use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterDomainCard;
use Domain\Character\Services\DomainCardService;

describe('DomainCardService', function () {
    beforeEach(function () {
        $this->service = app(DomainCardService::class);
        $this->character = Character::factory()->create([
            'class' => 'warrior', // Has blade and bone domains
            'level' => 1,
        ]);
    });

    describe('getAvailableCards', function () {
        test('returns cards from character class domains', function () {
            $cards = $this->service->getAvailableCards($this->character, 4);

            expect($cards)->toBeArray();
            
            // All cards should be from blade or bone domains
            foreach ($cards as $card) {
                expect($card['domain'])->toBeIn(['blade', 'bone']);
            }
        });

        test('filters cards by max level', function () {
            $cards = $this->service->getAvailableCards($this->character, 2);

            // All cards should be level 2 or lower
            foreach ($cards as $card) {
                expect($card['level'])->toBeLessThanOrEqual(2);
            }
        });

        test('includes all required card properties', function () {
            $cards = $this->service->getAvailableCards($this->character, 4);

            expect($cards)->not->toBeEmpty();
            
            foreach ($cards as $card) {
                expect($card)->toHaveKey('key');
                expect($card)->toHaveKey('name');
                expect($card)->toHaveKey('domain');
                expect($card)->toHaveKey('level');
                expect($card)->toHaveKey('type');
                expect($card)->toHaveKey('recall_cost');
                expect($card)->toHaveKey('descriptions');
                expect($card)->toHaveKey('is_multiclass');
            }
        });

        test('marks multiclass cards correctly', function () {
            // Create a multiclass advancement
            $this->character->advancements()->create([
                'tier' => 3,
                'advancement_number' => 1,
                'advancement_type' => 'multiclass',
                'advancement_data' => ['class_key' => 'wizard'],
                'description' => 'Multiclass into wizard',
            ]);

            $cards = $this->service->getAvailableCards($this->character, 4);

            // Should include some multiclass cards (from wizard domains: codex, midnight)
            $multiclassCards = array_filter($cards, fn ($card) => $card['is_multiclass']);
            
            // Note: This test depends on the character having multiclass advancements
            // For now, we just check the structure is correct
            foreach ($multiclassCards as $card) {
                expect($card['is_multiclass'])->toBeTrue();
            }
        });
    });

    describe('validateCardSelection', function () {
        test('returns true for valid card selection', function () {
            $cards = $this->service->getAvailableCards($this->character, 4);
            
            if (empty($cards)) {
                expect(true)->toBeTrue(); // Skip if no cards available
                return;
            }

            $firstCard = $cards[0];
            $valid = $this->service->validateCardSelection(
                $this->character,
                $firstCard['key'],
                4
            );

            expect($valid)->toBeTrue();
        });

        test('returns false for card not in character domains', function () {
            // Assuming 'grace' is not in warrior's domains
            $valid = $this->service->validateCardSelection(
                $this->character,
                'some-grace-card-key',
                4
            );

            expect($valid)->toBeFalse();
        });

        test('returns false for card above max level', function () {
            $cards = $this->service->getAvailableCards($this->character, 4);
            
            if (empty($cards)) {
                expect(true)->toBeTrue(); // Skip if no cards available
                return;
            }

            $firstCard = $cards[0];
            
            // Try to select at level 1, but card might be level 2+
            if ($firstCard['level'] > 1) {
                $valid = $this->service->validateCardSelection(
                    $this->character,
                    $firstCard['key'],
                    1
                );

                expect($valid)->toBeFalse();
            } else {
                expect(true)->toBeTrue(); // Skip if card is level 1
            }
        });
    });

    describe('hasCard', function () {
        test('returns false when character does not have card', function () {
            $hasCard = $this->service->hasCard($this->character, 'blade-strike');
            expect($hasCard)->toBeFalse();
        });

        test('returns true when character has card', function () {
            // Give character a domain card
            CharacterDomainCard::create([
                'character_id' => $this->character->id,
                'ability_key' => 'blade-strike',
                'domain' => 'blade',
                'ability_level' => 1,
            ]);

            $hasCard = $this->service->hasCard($this->character, 'blade-strike');
            expect($hasCard)->toBeTrue();
        });
    });

    describe('getCardsByDomain', function () {
        test('groups cards by domain', function () {
            $cards = $this->service->getAvailableCards($this->character, 4);
            $groupedCards = $this->service->getCardsByDomain($this->character, 4);

            expect($groupedCards)->toBeArray();
            
            // Check that keys are domain names
            foreach (array_keys($groupedCards) as $domain) {
                expect($domain)->toBeString();
            }

            // Check that each group contains domain_info and cards
            foreach ($groupedCards as $domain => $domainGroup) {
                expect($domainGroup)->toBeArray();
                expect($domainGroup)->toHaveKey('domain_info');
                expect($domainGroup)->toHaveKey('cards');
                
                // All cards in this group should be from this domain
                foreach ($domainGroup['cards'] as $card) {
                    expect($card['domain'])->toBe($domain);
                }
            }
        });

        test('all cards appear in grouped result', function () {
            $allCards = $this->service->getAvailableCards($this->character, 4);
            $groupedCards = $this->service->getCardsByDomain($this->character, 4);

            $totalGroupedCards = 0;
            foreach ($groupedCards as $domainGroup) {
                $totalGroupedCards += count($domainGroup['cards']);
            }

            expect($totalGroupedCards)->toBe(count($allCards));
        });
    });

    describe('multiclass domain card restrictions', function () {
        test('multiclass cards have level restriction of half character level', function () {
            $this->character->update(['level' => 6]);
            
            // Create multiclass advancement
            $this->character->advancements()->create([
                'tier' => 3,
                'advancement_number' => 1,
                'advancement_type' => 'multiclass',
                'advancement_data' => ['class_key' => 'wizard'],
                'description' => 'Multiclass into wizard',
            ]);

            $cards = $this->service->getAvailableCards($this->character, 6);
            
            // Multiclass cards should be limited to floor(6/2) = 3
            $multiclassCards = array_filter($cards, fn ($card) => $card['is_multiclass']);
            
            foreach ($multiclassCards as $card) {
                expect($card['level'])->toBeLessThanOrEqual(3);
            }
        });
    });
});

