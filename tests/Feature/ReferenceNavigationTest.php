<?php

declare(strict_types=1);

describe('Reference Navigation', function () {
    test('reference link appears in authenticated user navigation', function () {
        $user = \Domain\User\Models\User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200)
            ->assertSee('DaggerHeart Reference');
    });

    test('reference link appears in guest navigation', function () {
        $response = $this->get('/character-builder');

        $response->assertStatus(200)
            ->assertSee('DaggerHeart Reference');
    });

    test('reference navigation structure includes proper ordering', function () {
        $user = \Domain\User\Models\User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        // Check that Reference appears before Visual Range Checker in the dropdown
        $content = $response->getContent();
        $referencePos = strpos($content, 'DaggerHeart Reference');
        $rangePos = strpos($content, 'Visual Range Checker');

        expect($referencePos)->toBeLessThan($rangePos)
            ->and($referencePos)->not->toBeFalse()
            ->and($rangePos)->not->toBeFalse();
    });

    test('mobile navigation includes reference links', function () {
        $response = $this->get('/character-builder');

        $response->assertStatus(200)
            ->assertSee('DaggerHeart Reference');
    });
});

