<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;
use Domain\Character\Models\Character;

use function Pest\Laravel\actingAs;

describe('Room Nameplate Arrow Display', function () {
    beforeEach(function () {
        $this->gm = User::factory()->create();
        $this->room = Room::factory()->create([
            'creator_id' => $this->gm->id,
            'guest_count' => 2,
        ]);

        RoomParticipant::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->gm->id,
        ]);
    });

    test('character overlay container exists in video slots', function () {
        actingAs($this->gm);

        visit("/rooms/{$this->room->invite_code}/session")
            ->waitFor('.video-slot')
            ->assertPresent('.character-overlay');
    });

    test('nameplate has banner arrow element', function () {
        actingAs($this->gm);

        visit("/rooms/{$this->room->invite_code}/session")
            ->waitFor('.video-slot')
            ->assertPresent('.character-overlay')
            ->script("
                // Check for banner arrow in character overlay
                const overlay = document.querySelector('.character-overlay');
                const bannerArrow = overlay?.querySelector('[class*=\"-right-\"]');
                return bannerArrow !== null;
            ", function ($hasBannerArrow) {
                expect($hasBannerArrow)->toBeTrue();
            });
    });

    test('banner arrow has correct z-index', function () {
        actingAs($this->gm);

        visit("/rooms/{$this->room->invite_code}/session")
            ->waitFor('.video-slot')
            ->script("
                const overlay = document.querySelector('.character-overlay');
                const bannerArrow = overlay?.querySelector('[class*=\"-right-\"]');
                if (bannerArrow) {
                    const styles = window.getComputedStyle(bannerArrow);
                    return parseInt(styles.zIndex) || 0;
                }
                return 0;
            ", function ($zIndex) {
                expect($zIndex)->toBeGreaterThanOrEqual(20);
            });
    });

    test('banner arrow is positioned correctly', function () {
        actingAs($this->gm);

        visit("/rooms/{$this->room->invite_code}/session")
            ->waitFor('.video-slot')
            ->script("
                const overlay = document.querySelector('.character-overlay');
                const bannerArrow = overlay?.querySelector('[class*=\"-right-\"]');
                if (bannerArrow) {
                    const styles = window.getComputedStyle(bannerArrow);
                    return {
                        position: styles.position,
                        hasTransform: bannerArrow.style.transform !== '',
                    };
                }
                return null;
            ", function ($positioning) {
                expect($positioning)->not->toBeNull();
                expect($positioning['position'])->toBe('absolute');
            });
    });

    test('character info container has correct structure', function () {
        actingAs($this->gm);

        visit("/rooms/{$this->room->invite_code}/session")
            ->waitFor('.video-slot')
            ->assertPresent('.character-overlay .character-name')
            ->assertPresent('.character-overlay .character-class')
            ->assertPresent('.character-overlay .character-banner-container');
    });

    test('banner right component is rendered', function () {
        actingAs($this->gm);

        visit("/rooms/{$this->room->invite_code}/session")
            ->waitFor('.video-slot')
            ->script("
                const overlay = document.querySelector('.character-overlay');
                const svg = overlay?.querySelector('svg');
                return svg !== null;
            ", function ($hasSvg) {
                expect($hasSvg)->toBeTrue();
            });
    });

    test('nameplate container has proper overflow handling', function () {
        actingAs($this->gm);

        visit("/rooms/{$this->room->invite_code}/session")
            ->waitFor('.video-slot')
            ->script("
                const namePlateContainer = document.querySelector('.character-overlay > div');
                if (namePlateContainer) {
                    const styles = window.getComputedStyle(namePlateContainer);
                    return styles.overflow;
                }
                return null;
            ", function ($overflow) {
                expect($overflow)->toBe('visible');
            });
    });

    test('character overlay is hidden by default', function () {
        actingAs($this->gm);

        visit("/rooms/{$this->room->invite_code}/session")
            ->waitFor('.video-slot')
            ->assertPresent('.character-overlay.hidden');
    });

    test('character overlay becomes visible when slot is occupied', function () {
        $player = User::factory()->create();
        $character = Character::factory()->create([
            'user_id' => $player->id,
            'name' => 'Test Character',
            'class' => 'warrior',
        ]);

        RoomParticipant::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $player->id,
            'character_id' => $character->id,
        ]);

        actingAs($player);

        visit("/rooms/{$this->room->invite_code}/session")
            ->waitFor('.video-slot')
            ->pause(1000);
        
        // Character overlay visibility is controlled by JS when user joins
    })->skip('Requires full WebRTC join flow');

    test('banner arrow width and height are correctly styled', function () {
        actingAs($this->gm);

        visit("/rooms/{$this->room->invite_code}/session")
            ->waitFor('.video-slot')
            ->script("
                const overlay = document.querySelector('.character-overlay');
                const bannerArrow = overlay?.querySelector('[style*=\"width\"]');
                if (bannerArrow) {
                    return bannerArrow.style.cssText;
                }
                return '';
            ", function ($styles) {
                expect($styles)->toContain('width: 72px');
                expect($styles)->toContain('height: 55px');
            });
    });
});

