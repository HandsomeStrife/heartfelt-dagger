<?php

declare(strict_types=1);

namespace Tests\Feature;

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecordingSettings;
use Domain\User\Models\User;
use Domain\User\Models\UserStorageAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SttProviderSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_browser_stt_provider_works_by_default(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        // Create recording settings with STT enabled but no provider specified
        RoomRecordingSettings::create([
            'room_id' => $room->id,
            'recording_enabled' => false,
            'stt_enabled' => true,
            'stt_provider' => null, // Should default to browser
            'stt_account_id' => null,
            'stt_consent_requirement' => 'optional',
            'recording_consent_requirement' => 'optional',
        ]);

        $response = $this->actingAs($user)->get("/api/rooms/{$room->id}/stt-config");

        $response->assertOk()
            ->assertJson([
                'provider' => 'browser',
                'config' => [],
            ]);
    }

    public function test_assemblyai_stt_provider_requires_account(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        // Create recording settings with AssemblyAI provider but no account
        RoomRecordingSettings::create([
            'room_id' => $room->id,
            'recording_enabled' => false,
            'stt_enabled' => true,
            'stt_provider' => 'assemblyai',
            'stt_account_id' => null,
            'stt_consent_requirement' => 'optional',
            'recording_consent_requirement' => 'optional',
        ]);

        $response = $this->actingAs($user)->get("/api/rooms/{$room->id}/stt-config");

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'AssemblyAI account not configured',
            ]);
    }

    public function test_assemblyai_stt_provider_works_with_account(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        // Create AssemblyAI account
        $assemblyAIAccount = UserStorageAccount::create([
            'user_id' => $user->id,
            'provider' => 'assemblyai',
            'display_name' => 'Test AssemblyAI Account',
            'encrypted_credentials' => [
                'api_key' => 'test_api_key_12345',
            ],
            'is_active' => true,
        ]);

        // Create recording settings with AssemblyAI provider
        RoomRecordingSettings::create([
            'room_id' => $room->id,
            'recording_enabled' => false,
            'stt_enabled' => true,
            'stt_provider' => 'assemblyai',
            'stt_account_id' => $assemblyAIAccount->id,
            'stt_consent_requirement' => 'optional',
            'recording_consent_requirement' => 'optional',
        ]);

        $response = $this->actingAs($user)->get("/api/rooms/{$room->id}/stt-config");

        $response->assertOk()
            ->assertJson([
                'provider' => 'assemblyai',
                'config' => [
                    'api_key' => 'test_api_key_12345',
                ],
            ]);
    }

    public function test_stt_config_requires_authentication(): void
    {
        $room = Room::factory()->create();

        $response = $this->get("/api/rooms/{$room->id}/stt-config");

        $response->assertStatus(302); // Redirect to login
    }

    public function test_stt_config_requires_room_access(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $otherUser->id]);

        // Create a campaign room that the user doesn't have access to
        $campaign = \Domain\Campaign\Models\Campaign::factory()->create(['creator_id' => $otherUser->id]);
        $room->update(['campaign_id' => $campaign->id]);

        $response = $this->actingAs($user)->get("/api/rooms/{$room->id}/stt-config");

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'Access denied',
            ]);
    }

    public function test_stt_config_requires_stt_enabled(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        // Create recording settings with STT disabled
        RoomRecordingSettings::create([
            'room_id' => $room->id,
            'recording_enabled' => false,
            'stt_enabled' => false,
            'stt_provider' => 'browser',
            'stt_account_id' => null,
            'stt_consent_requirement' => 'optional',
            'recording_consent_requirement' => 'optional',
        ]);

        $response = $this->actingAs($user)->get("/api/rooms/{$room->id}/stt-config");

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'STT not enabled for this room',
            ]);
    }
}
