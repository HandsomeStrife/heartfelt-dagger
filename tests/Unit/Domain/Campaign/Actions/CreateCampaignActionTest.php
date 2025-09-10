<?php

declare(strict_types=1);
use Domain\Campaign\Actions\CreateCampaignAction;
use Domain\Campaign\Data\CampaignData;
use Domain\Campaign\Data\CreateCampaignData;
use Domain\Campaign\Enums\CampaignStatus;
use Domain\User\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->action = new CreateCampaignAction;
});
it('creates campaign successfully', function () {
    $creator = User::factory()->create();
    $createData = CreateCampaignData::from([
        'name' => 'The Lost Mines of Phandelver',
        'description' => 'A classic D&D adventure adapted for DaggerHeart',
    ]);

    $result = $this->action->execute($createData, $creator);

    expect($result)->toBeInstanceOf(CampaignData::class);
    expect($result->name)->toEqual('The Lost Mines of Phandelver');
    expect($result->description)->toEqual('A classic D&D adventure adapted for DaggerHeart');
    expect($result->creator_id)->toEqual($creator->id);
    expect($result->status)->toEqual(CampaignStatus::ACTIVE);
});
it('auto generates invite code', function () {
    $creator = User::factory()->create();
    $createData = CreateCampaignData::from([
        'name' => 'Test Campaign',
        'description' => 'Test Description',
    ]);

    $result = $this->action->execute($createData, $creator);

    expect($result->invite_code)->not->toBeNull();
    expect(strlen($result->invite_code))->toEqual(8);
    expect($result->invite_code)->toMatch('/^[A-Z0-9]{8}$/');
});
it('auto generates campaign code', function () {
    $creator = User::factory()->create();
    $createData = CreateCampaignData::from([
        'name' => 'Test Campaign',
        'description' => 'Test Description',
    ]);

    $result = $this->action->execute($createData, $creator);

    expect($result->campaign_code)->not->toBeNull();
    expect(strlen($result->campaign_code))->toEqual(8);
    expect($result->campaign_code)->toMatch('/^[A-Z0-9]{8}$/');
});
it('ensures codes are different', function () {
    $creator = User::factory()->create();
    $createData = CreateCampaignData::from([
        'name' => 'Test Campaign',
        'description' => 'Test Description',
    ]);

    $result = $this->action->execute($createData, $creator);

    expect($result->invite_code)->not->toEqual($result->campaign_code);
});
it('persists campaign to database', function () {
    $creator = User::factory()->create();
    $createData = CreateCampaignData::from([
        'name' => 'Persistent Campaign',
        'description' => 'This should be saved to the database',
    ]);

    $result = $this->action->execute($createData, $creator);

    \Pest\Laravel\assertDatabaseHas('campaigns', [
        'id' => $result->id,
        'name' => 'Persistent Campaign',
        'description' => 'This should be saved to the database',
        'creator_id' => $creator->id,
        'invite_code' => $result->invite_code,
        'campaign_code' => $result->campaign_code,
        'status' => CampaignStatus::ACTIVE->value,
    ]);
});
it('loads creator relationship', function () {
    $creator = User::factory()->create(['username' => 'dungeon_master']);
    $createData = CreateCampaignData::from([
        'name' => 'Test Campaign',
        'description' => 'Test Description',
    ]);

    $result = $this->action->execute($createData, $creator);

    expect($result->creator)->not->toBeNull();
    expect($result->creator->username)->toEqual('dungeon_master');
    expect($result->creator->id)->toEqual($creator->id);
});
it('creates campaigns with unique codes', function () {
    $creator = User::factory()->create();
    $createData1 = CreateCampaignData::from([
        'name' => 'Campaign One',
        'description' => 'First campaign',
    ]);
    $createData2 = CreateCampaignData::from([
        'name' => 'Campaign Two',
        'description' => 'Second campaign',
    ]);

    $result1 = $this->action->execute($createData1, $creator);
    $result2 = $this->action->execute($createData2, $creator);

    expect($result1->invite_code)->not->toEqual($result2->invite_code);
    expect($result1->campaign_code)->not->toEqual($result2->campaign_code);
});
it('handles long names and descriptions', function () {
    $creator = User::factory()->create();
    $createData = CreateCampaignData::from([
        'name' => str_repeat('A', 100), // Max length
        'description' => str_repeat('B', 1000), // Max length
    ]);

    $result = $this->action->execute($createData, $creator);

    expect($result->name)->toEqual(str_repeat('A', 100));
    expect($result->description)->toEqual(str_repeat('B', 1000));
});
it('associates creator correctly', function () {
    $creator1 = User::factory()->create();
    $creator2 = User::factory()->create();

    $createData1 = CreateCampaignData::from([
        'name' => 'Creator 1 Campaign',
        'description' => 'Campaign by creator 1',
    ]);
    $createData2 = CreateCampaignData::from([
        'name' => 'Creator 2 Campaign',
        'description' => 'Campaign by creator 2',
    ]);

    $result1 = $this->action->execute($createData1, $creator1);
    $result2 = $this->action->execute($createData2, $creator2);

    expect($result1->creator_id)->toEqual($creator1->id);
    expect($result2->creator_id)->toEqual($creator2->id);
    expect($result1->creator_id)->not->toEqual($result2->creator_id);
});
it('initializes member count as null', function () {
    $creator = User::factory()->create();
    $createData = CreateCampaignData::from([
        'name' => 'Test Campaign',
        'description' => 'Test Description',
    ]);

    $result = $this->action->execute($createData, $creator);

    // Member count should be null in the result since it's loaded separately
    expect($result->member_count)->toBeNull();
});
