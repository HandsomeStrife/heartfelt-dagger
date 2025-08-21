<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Campaign\Data;

use Domain\Campaign\Data\CampaignData;
use Domain\Campaign\Enums\CampaignStatus;
use Domain\Campaign\Models\Campaign;
use Domain\User\Data\UserData;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Wireable;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CampaignDataTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_implements_wireable_interface(): void
    {
        $campaignData = new CampaignData(
            id: 1,
            name: 'Test Campaign',
            description: 'Test Description',
            creator_id: 1,
            invite_code: 'ABC12345',
            campaign_code: 'XYZ67890',
            status: CampaignStatus::ACTIVE
        );

        $this->assertInstanceOf(Wireable::class, $campaignData);
    }

    #[Test]
    public function it_creates_from_array(): void
    {
        $data = [
            'id' => 1,
            'name' => 'Array Campaign',
            'description' => 'Created from array',
            'creator_id' => 2,
            'invite_code' => 'ARRAY123',
            'campaign_code' => 'CAMP4567',
            'status' => CampaignStatus::ACTIVE,
            'created_at' => '2023-01-01 12:00:00',
            'updated_at' => '2023-01-02 12:00:00',
            'member_count' => 5,
        ];

        $campaignData = CampaignData::from($data);

        $this->assertEquals(1, $campaignData->id);
        $this->assertEquals('Array Campaign', $campaignData->name);
        $this->assertEquals('Created from array', $campaignData->description);
        $this->assertEquals(2, $campaignData->creator_id);
        $this->assertEquals('ARRAY123', $campaignData->invite_code);
        $this->assertEquals('CAMP4567', $campaignData->campaign_code);
        $this->assertEquals(CampaignStatus::ACTIVE, $campaignData->status);
        $this->assertEquals('2023-01-01 12:00:00', $campaignData->created_at);
        $this->assertEquals('2023-01-02 12:00:00', $campaignData->updated_at);
        $this->assertEquals(5, $campaignData->member_count);
    }

    #[Test]
    public function it_creates_from_model(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create([
            'name' => 'Model Campaign',
            'description' => 'Created from model',
            'creator_id' => $user->id,
            'status' => CampaignStatus::ARCHIVED,
        ]);

        $campaignData = CampaignData::from($campaign);

        $this->assertEquals($campaign->id, $campaignData->id);
        $this->assertEquals('Model Campaign', $campaignData->name);
        $this->assertEquals('Created from model', $campaignData->description);
        $this->assertEquals($user->id, $campaignData->creator_id);
        $this->assertEquals($campaign->invite_code, $campaignData->invite_code);
        $this->assertEquals($campaign->campaign_code, $campaignData->campaign_code);
        $this->assertEquals(CampaignStatus::ARCHIVED, $campaignData->status);
        $this->assertNotNull($campaignData->created_at);
        $this->assertNotNull($campaignData->updated_at);
    }

    #[Test]
    public function it_creates_from_model_with_relationships(): void
    {
        $user = User::factory()->create(['username' => 'test_creator']);
        $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
        $campaign->load('creator');

        $campaignData = CampaignData::from($campaign);

        $this->assertNotNull($campaignData->creator);
        $this->assertInstanceOf(UserData::class, $campaignData->creator);
        $this->assertEquals('test_creator', $campaignData->creator->username);
        $this->assertEquals($user->id, $campaignData->creator->id);
    }

    #[Test]
    public function it_handles_null_optional_fields(): void
    {
        $campaignData = new CampaignData(
            id: 1,
            name: 'Minimal Campaign',
            description: 'Minimal description',
            creator_id: 1,
            invite_code: 'MIN12345',
            campaign_code: 'MINC6789',
            status: CampaignStatus::ACTIVE,
            created_at: null,
            updated_at: null,
            creator: null,
            member_count: null
        );

        $this->assertNull($campaignData->created_at);
        $this->assertNull($campaignData->updated_at);
        $this->assertNull($campaignData->creator);
        $this->assertNull($campaignData->member_count);
    }

    #[Test]
    public function it_handles_all_status_types(): void
    {
        $statuses = [
            CampaignStatus::ACTIVE,
            CampaignStatus::ARCHIVED,
            CampaignStatus::COMPLETED,
            CampaignStatus::PAUSED,
        ];

        foreach ($statuses as $status) {
            $campaignData = new CampaignData(
                id: 1,
                name: 'Status Test',
                description: 'Testing status',
                creator_id: 1,
                invite_code: 'STATUS12',
                campaign_code: 'STAT3456',
                status: $status
            );

            $this->assertEquals($status, $campaignData->status);
            $this->assertInstanceOf(CampaignStatus::class, $campaignData->status);
        }
    }

    #[Test]
    public function it_creates_with_member_count_from_array(): void
    {
        $data = [
            'id' => 1,
            'name' => 'Count Test',
            'description' => 'Testing member count',
            'creator_id' => 1,
            'invite_code' => 'COUNT123',
            'campaign_code' => 'CNT45678',
            'status' => CampaignStatus::ACTIVE,
            'member_count' => 42,
        ];

        $campaignData = CampaignData::from($data);

        $this->assertEquals(42, $campaignData->member_count);
    }

    #[Test]
    public function it_preserves_string_status_from_array(): void
    {
        $data = [
            'id' => 1,
            'name' => 'String Status',
            'description' => 'Testing string status',
            'creator_id' => 1,
            'invite_code' => 'STR12345',
            'campaign_code' => 'STRG6789',
            'status' => 'paused', // String status
        ];

        $campaignData = CampaignData::from($data);

        $this->assertEquals(CampaignStatus::PAUSED, $campaignData->status);
    }

    #[Test]
    public function it_works_with_livewire_toLivewire(): void
    {
        $campaignData = new CampaignData(
            id: 1,
            name: 'Livewire Test',
            description: 'Testing Livewire compatibility',
            creator_id: 1,
            invite_code: 'LIVE1234',
            campaign_code: 'LW567890',
            status: CampaignStatus::ACTIVE,
            member_count: 10
        );

        $livewireData = $campaignData->toLivewire();

        $this->assertIsArray($livewireData);
        $this->assertArrayHasKey('id', $livewireData);
        $this->assertArrayHasKey('name', $livewireData);
        $this->assertArrayHasKey('status', $livewireData);
        $this->assertEquals(1, $livewireData['id']);
        $this->assertEquals('Livewire Test', $livewireData['name']);
    }

    #[Test]
    public function it_works_with_livewire_fromLivewire(): void
    {
        $livewireData = [
            'id' => 2,
            'name' => 'From Livewire',
            'description' => 'Created from Livewire data',
            'creator_id' => 2,
            'invite_code' => 'FROM1234',
            'campaign_code' => 'FLW56789',
            'status' => CampaignStatus::COMPLETED,
            'member_count' => 8,
        ];

        $campaignData = CampaignData::fromLivewire($livewireData);

        $this->assertInstanceOf(CampaignData::class, $campaignData);
        $this->assertEquals(2, $campaignData->id);
        $this->assertEquals('From Livewire', $campaignData->name);
        $this->assertEquals(CampaignStatus::COMPLETED, $campaignData->status);
        $this->assertEquals(8, $campaignData->member_count);
    }

    #[Test]
    public function it_handles_large_member_counts(): void
    {
        $campaignData = new CampaignData(
            id: 1,
            name: 'Large Campaign',
            description: 'Testing large member count',
            creator_id: 1,
            invite_code: 'LARGE123',
            campaign_code: 'LRG45678',
            status: CampaignStatus::ACTIVE,
            member_count: 999999
        );

        $this->assertEquals(999999, $campaignData->member_count);
    }

    #[Test]
    public function it_handles_zero_member_count(): void
    {
        $campaignData = new CampaignData(
            id: 1,
            name: 'Empty Campaign',
            description: 'No members yet',
            creator_id: 1,
            invite_code: 'EMPTY123',
            campaign_code: 'EMP45678',
            status: CampaignStatus::ACTIVE,
            member_count: 0
        );

        $this->assertEquals(0, $campaignData->member_count);
    }
}
