<?php

namespace Tests\Unit;

use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created(): void
    {
        $user = User::create([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('testuser', $user->username);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_user_has_fillable_attributes(): void
    {
        $fillable = ['username', 'email', 'password'];
        $user = new User;

        $this->assertEquals($fillable, $user->getFillable());
    }

    public function test_user_has_hidden_attributes(): void
    {
        $hidden = ['password', 'remember_token'];
        $user = new User;

        $this->assertEquals($hidden, $user->getHidden());
    }

    public function test_password_is_automatically_hashed(): void
    {
        $user = User::factory()->create([
            'password' => 'password123',
        ]);

        $this->assertNotEquals('password123', $user->password);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_email_verified_at_is_cast_to_datetime(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->email_verified_at);
    }

    public function test_user_factory_creates_valid_user(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->username);
        $this->assertNotNull($user->email);
        $this->assertNotNull($user->password);
        $this->assertTrue(str_contains($user->email, '@'));
    }

    public function test_user_factory_can_override_attributes(): void
    {
        $user = User::factory()->create([
            'username' => 'customuser',
            'email' => 'custom@example.com',
        ]);

        $this->assertEquals('customuser', $user->username);
        $this->assertEquals('custom@example.com', $user->email);
    }

    public function test_user_model_uses_correct_table(): void
    {
        $user = new User;
        $this->assertEquals('users', $user->getTable());
    }

    public function test_user_model_has_timestamps(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->created_at);
        $this->assertNotNull($user->updated_at);
    }

    public function test_user_can_be_found_by_email(): void
    {
        $user = User::factory()->create([
            'email' => 'findme@example.com',
        ]);

        $foundUser = User::where('email', 'findme@example.com')->first();

        $this->assertEquals($user->id, $foundUser->id);
    }

    public function test_user_can_be_found_by_username(): void
    {
        $user = User::factory()->create([
            'username' => 'findmeuser',
        ]);

        $foundUser = User::where('username', 'findmeuser')->first();

        $this->assertEquals($user->id, $foundUser->id);
    }

    public function test_remember_token_can_be_set(): void
    {
        $user = User::factory()->create();
        $token = 'test-remember-token';

        $user->remember_token = $token;
        $user->save();

        $this->assertEquals($token, $user->fresh()->remember_token);
    }

    public function test_user_attributes_are_properly_cast(): void
    {
        $user = User::factory()->create();

        $casts = $user->getCasts();

        $this->assertArrayHasKey('email_verified_at', $casts);
        $this->assertArrayHasKey('password', $casts);
        $this->assertEquals('datetime', $casts['email_verified_at']);
        $this->assertEquals('hashed', $casts['password']);
    }

    public function test_user_can_be_soft_deleted_if_trait_is_added(): void
    {
        // This test assumes you might add soft deletes in the future
        $user = User::factory()->create();

        // For now, just test that delete works normally
        $userId = $user->id;
        $user->delete();

        $this->assertNull(User::find($userId));
    }
}
