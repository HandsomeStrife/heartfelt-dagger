<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DiscordRedirectTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function discord_route_redirects_to_discord_url(): void
    {
        $response = $this->get('/discord');

        $response->assertStatus(302);
        $response->assertRedirect('https://discord.gg/dNAkDYevGx');
    }

    #[Test]
    public function discord_route_is_accessible_to_guests(): void
    {
        // Guests should be able to access the Discord redirect
        $response = $this->get('/discord');

        $response->assertStatus(302);
    }

    #[Test]
    public function discord_route_is_accessible_to_authenticated_users(): void
    {
        $user = \Domain\User\Models\User::factory()->create();

        $response = $this->actingAs($user)->get('/discord');

        $response->assertStatus(302);
        $response->assertRedirect('https://discord.gg/dNAkDYevGx');
    }
}
