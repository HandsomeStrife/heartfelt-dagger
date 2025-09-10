<?php

declare(strict_types=1);

use Domain\User\Models\User;
use Domain\User\Notifications\PasswordResetNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('password reset email contains correct content and styling', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'adventurer@example.com',
    ]);

    // Trigger password reset
    $this->post('/forgot-password', [
        'email' => 'adventurer@example.com',
    ]);

    // Assert notification was sent
    Notification::assertSentTo($user, PasswordResetNotification::class, function ($notification) use ($user) {
        $mailMessage = $notification->toMail($user);

        // Check subject
        expect($mailMessage->subject)->toBe('Reset Your HeartfeltDagger Password');

        // Check greeting
        expect($mailMessage->greeting)->toBe('Greetings, Adventurer!');

        // Check content includes key phrases
        $content = collect($mailMessage->introLines);
        expect($content->first())->toContain('password reset request');
        expect($content->last())->toContain('60 minutes');

        // Check action button
        expect($mailMessage->actionText)->toBe('Reset Password');
        expect($mailMessage->actionUrl)->toContain('/reset-password/');
        expect($mailMessage->actionUrl)->toContain('email=adventurer@example.com');

        // Check salutation
        expect($mailMessage->salutation)->toBe('The HeartfeltDagger Team');

        return true;
    });
});

test('password reset email generates valid reset URL', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'test@daggerheart.com',
    ]);

    // Trigger password reset
    $this->post('/forgot-password', [
        'email' => 'test@daggerheart.com',
    ]);

    // Check the generated URL structure
    Notification::assertSentTo($user, PasswordResetNotification::class, function ($notification) use ($user) {
        $mailMessage = $notification->toMail($user);
        $resetUrl = $mailMessage->actionUrl;

        // Parse the URL
        $parsedUrl = parse_url($resetUrl);
        parse_str($parsedUrl['query'], $queryParams);

        // Verify URL structure
        expect($parsedUrl['path'])->toMatch('/^\/reset-password\/[a-zA-Z0-9]{64}$/');
        expect($queryParams['email'])->toBe('test@daggerheart.com');

        // Verify the URL is accessible
        $response = $this->get($resetUrl);
        $response->assertStatus(200);
        $response->assertSee('Choose New Password');

        return true;
    });
});

test('password reset email can be sent via different mail drivers', function () {
    // This test shows how to configure different mail drivers
    $originalDriver = config('mail.default');

    try {
        // Test with log driver (default)
        config(['mail.default' => 'log']);

        $user = User::factory()->create(['email' => 'log-test@example.com']);

        $response = $this->post('/forgot-password', [
            'email' => 'log-test@example.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'We have emailed your password reset link!');

        // Test with array driver (for testing)
        config(['mail.default' => 'array']);

        $user2 = User::factory()->create(['email' => 'array-test@example.com']);

        $response2 = $this->post('/forgot-password', [
            'email' => 'array-test@example.com',
        ]);

        $response2->assertRedirect();
        $response2->assertSessionHas('status', 'We have emailed your password reset link!');

    } finally {
        // Restore original driver
        config(['mail.default' => $originalDriver]);
    }
});
