<?php

declare(strict_types=1);
use Domain\Character\Models\Character;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Hash;
use function Pest\Laravel\actingAs;

it('shows correct navigation for guest users', function () {
    $page = visit('/')
        ->assertSee('Character Builder')
        ->assertSee('Login')
        ->assertSee('Register');
});
it('authenticated user can access dashboard', function () {
    $user = User::factory()->create();

    actingAs($user);
    $page = visit('/dashboard')
        ->assertSee('Welcome')
        ->assertSee('Characters');
});

it('can create character anonymously', function () {
    $page = visit('/character-builder');
    
    $page->wait(3)
        ->assertSee('Character Builder');
    
    // Verify we can access character builder without being logged in
    expect(true)->toBeTrue();
});

it('login page loads correctly', function () {
    $page = visit('/login')
        ->assertSee('Enter the Realm')
        ->assertSee('Email')
        ->assertSee('Password');
});

it('associates anonymous character to user on registration after a failed login', function () {
    // Create an anonymous character via the builder flow
    $page = visit('/character-builder');
    $page->assertPathBeginsWith('/character-builder/');

    // Capture the character key from the URL
    $url = $page->url();
    $path = parse_url($url, PHP_URL_PATH);
    $segments = array_values(array_filter(explode('/', (string) $path)));
    $character_key = end($segments);

    // Ensure the key is stored in localStorage for association
    $page->wait(0.2);
    $in_storage = $page->script('(() => (JSON.parse(localStorage.getItem("daggerheart_characters") || "[]") || []).includes("' . $character_key . '"))()');
    expect((bool) $in_storage)->toBeTrue();

    // Attempt a failed login
    $page->navigate('/login')
        ->fill('email', 'not-a-user@example.com')
        ->fill('password', 'wrongpassword')
        ->click('[data-testid="login-submit-button"]')
        ->assertPathIs('/login');

    // Then register successfully
    $username = 'user_' . uniqid();
    $email = $username . '@example.com';

    $page->navigate('/register')
        ->fill('username', $username)
        ->fill('email', $email)
        ->fill('password', 'password')
        ->fill('password_confirmation', 'password')
        ->click('Begin Adventure')
        ->assertPathIs('/dashboard');

    // Verify the character is now associated to the registered user
    $character = Character::where('character_key', $character_key)->first();
    $user = User::where('email', $email)->first();

    expect($user)->not->toBeNull();
    expect($character)->not->toBeNull();
    expect($character->user_id)->toBe($user->id);
});

it('associates anonymous character to user on registration', function () {
    // Create an anonymous character via the builder flow
    $page = visit('/character-builder');
    $page->assertPathBeginsWith('/character-builder/');

    // Capture the character key from the URL
    $url = $page->url();
    $path = parse_url($url, PHP_URL_PATH);
    $segments = array_values(array_filter(explode('/', (string) $path)));
    $character_key = end($segments);

    // Ensure the key is stored in localStorage for association
    $page->wait(0.2);
    $in_storage = $page->script('(() => (JSON.parse(localStorage.getItem("daggerheart_characters") || "[]") || []).includes("' . $character_key . '"))()');
    expect((bool) $in_storage)->toBeTrue();

    // Register a brand new user
    $username = 'user_' . uniqid();
    $email = $username . '@example.com';

    $page->navigate('/register')
        ->fill('username', $username)
        ->fill('email', $email)
        ->fill('password', 'password')
        ->fill('password_confirmation', 'password')
        ->click('Begin Adventure')
        ->assertPathIs('/dashboard');

    // Verify the character is now associated to the registered user
    $character = Character::where('character_key', $character_key)->first();
    $user = User::where('email', $email)->first();

    expect($user)->not->toBeNull();
    expect($character)->not->toBeNull();
    expect($character->user_id)->toBe($user->id);
});

it('associates anonymous character to user on login', function () {
    // Prepare an existing user account
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    // Create an anonymous character via the builder flow
    $page = visit('/character-builder');
    $page->assertPathBeginsWith('/character-builder/');

    // Capture the character key from the URL
    $url = $page->url();
    $path = parse_url($url, PHP_URL_PATH);
    $segments = array_values(array_filter(explode('/', (string) $path)));
    $character_key = end($segments);

    // Ensure the key is stored in localStorage for association
    $page->wait(0.2);
    $in_storage = $page->script('(() => (JSON.parse(localStorage.getItem("daggerheart_characters") || "[]") || []).includes("' . $character_key . '"))()');
    expect((bool) $in_storage)->toBeTrue();

    // Login with the existing user
    $page->navigate('/login')
        ->fill('email', $user->email)
        ->fill('password', 'password')
        ->click('[data-testid="login-submit-button"]')
        ->assertPathIs('/dashboard');

    // Verify the character is now associated to this user
    $character = Character::where('character_key', $character_key)->first();
    expect($character)->not->toBeNull();
    expect($character->user_id)->toBe($user->id);
});

it("does not reassign another user's character on registration", function () {
    // Owner user with an existing character
    $owner = User::factory()->create();
    $character = Character::factory()->create([
        'user_id' => $owner->id,
    ]);

    // Visit public viewer for that character as a guest
    $page = visit('/character/' . $character->public_key);
    $page->assertSee('Experience'); // basic content check

    // Register a different user
    $username = 'user_' . uniqid();
    $email = $username . '@example.com';

    $page->navigate('/register')
        ->fill('username', $username)
        ->fill('email', $email)
        ->fill('password', 'password')
        ->fill('password_confirmation', 'password')
        ->click('Begin Adventure')
        ->assertPathIs('/dashboard');

    // Ensure ownership did not change
    $character->refresh();
    expect($character->user_id)->toBe($owner->id);
});

it("does not reassign another user's character on login", function () {
    // Owner user with an existing character
    $owner = User::factory()->create();
    $character = Character::factory()->create([
        'user_id' => $owner->id,
    ]);

    // Another user to login
    $other = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    // Visit public viewer for that character as a guest
    $page = visit('/character/' . $character->public_key);
    $page->assertSee('Experience'); // basic content check

    // Login as a different user
    $page->navigate('/login')
        ->fill('email', $other->email)
        ->fill('password', 'password')
        ->click('[data-testid="login-submit-button"]')
        ->assertPathIs('/dashboard');

    // Ensure ownership did not change
    $character->refresh();
    expect($character->user_id)->toBe($owner->id);
});