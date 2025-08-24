<?php

declare(strict_types=1);
use Domain\Campaign\Data\CampaignData;
use Domain\Campaign\Enums\CampaignStatus;
use Domain\Campaign\Models\Campaign;
use Domain\User\Data\UserData;
use Domain\User\Models\User;
use Livewire\Wireable;
use PHPUnit\Framework\Attributes\Test;
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('implements wireable interface', function () {
    $campaignData = new CampaignData(
        id: 1,
        name: 'Test Campaign',
        description: 'Test Description',
        creator_id: 1,
        invite_code: 'ABC12345',
        campaign_code: 'XYZ67890',
        status: CampaignStatus::ACTIVE
    );

    expect($campaignData)->toBeInstanceOf(Wireable::class);
});
it('creates from array', function () {
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

    expect($campaignData->id)->toEqual(1);
    expect($campaignData->name)->toEqual('Array Campaign');
    expect($campaignData->description)->toEqual('Created from array');
    expect($campaignData->creator_id)->toEqual(2);
    expect($campaignData->invite_code)->toEqual('ARRAY123');
    expect($campaignData->campaign_code)->toEqual('CAMP4567');
    expect($campaignData->status)->toEqual(CampaignStatus::ACTIVE);
    expect($campaignData->created_at)->toEqual('2023-01-01 12:00:00');
    expect($campaignData->updated_at)->toEqual('2023-01-02 12:00:00');
    expect($campaignData->member_count)->toEqual(5);
});
it('creates from model', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create([
        'name' => 'Model Campaign',
        'description' => 'Created from model',
        'creator_id' => $user->id,
        'status' => CampaignStatus::ARCHIVED,
    ]);

    $campaignData = CampaignData::from($campaign);

    expect($campaignData->id)->toEqual($campaign->id);
    expect($campaignData->name)->toEqual('Model Campaign');
    expect($campaignData->description)->toEqual('Created from model');
    expect($campaignData->creator_id)->toEqual($user->id);
    expect($campaignData->invite_code)->toEqual($campaign->invite_code);
    expect($campaignData->campaign_code)->toEqual($campaign->campaign_code);
    expect($campaignData->status)->toEqual(CampaignStatus::ARCHIVED);
    expect($campaignData->created_at)->not->toBeNull();
    expect($campaignData->updated_at)->not->toBeNull();
});
it('creates from model with relationships', function () {
    $user = User::factory()->create(['username' => 'test_creator']);
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
    $campaign->load('creator');

    $campaignData = CampaignData::from($campaign);

    expect($campaignData->creator)->not->toBeNull();
    expect($campaignData->creator)->toBeInstanceOf(UserData::class);
    expect($campaignData->creator->username)->toEqual('test_creator');
    expect($campaignData->creator->id)->toEqual($user->id);
});
it('handles null optional fields', function () {
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

    expect($campaignData->created_at)->toBeNull();
    expect($campaignData->updated_at)->toBeNull();
    expect($campaignData->creator)->toBeNull();
    expect($campaignData->member_count)->toBeNull();
});
it('handles all status types', function () {
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

        expect($campaignData->status)->toEqual($status);
        expect($campaignData->status)->toBeInstanceOf(CampaignStatus::class);
    }
});
it('creates with member count from array', function () {
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

    expect($campaignData->member_count)->toEqual(42);
});
it('preserves string status from array', function () {
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

    expect($campaignData->status)->toEqual(CampaignStatus::PAUSED);
});
it('works with livewire to livewire', function () {
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

    expect($livewireData)->toBeArray();
    expect($livewireData)->toHaveKey('id');
    expect($livewireData)->toHaveKey('name');
    expect($livewireData)->toHaveKey('status');
    expect($livewireData['id'])->toEqual(1);
    expect($livewireData['name'])->toEqual('Livewire Test');
});
it('works with livewire from livewire', function () {
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

    expect($campaignData)->toBeInstanceOf(CampaignData::class);
    expect($campaignData->id)->toEqual(2);
    expect($campaignData->name)->toEqual('From Livewire');
    expect($campaignData->status)->toEqual(CampaignStatus::COMPLETED);
    expect($campaignData->member_count)->toEqual(8);
});
it('handles large member counts', function () {
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

    expect($campaignData->member_count)->toEqual(999999);
});
it('handles zero member count', function () {
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

    expect($campaignData->member_count)->toEqual(0);
});
