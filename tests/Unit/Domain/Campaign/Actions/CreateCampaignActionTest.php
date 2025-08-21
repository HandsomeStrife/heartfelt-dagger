<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Campaign\Actions;

use Domain\Campaign\Actions\CreateCampaignAction;
use Domain\Campaign\Data\CampaignData;
use Domain\Campaign\Data\CreateCampaignData;
use Domain\Campaign\Enums\CampaignStatus;
use Domain\Campaign\Models\Campaign;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateCampaignActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateCampaignAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new CreateCampaignAction();
    }

    #[Test]
    public function it_creates_campaign_successfully(): void
    {
        $creator = User::factory()->create();
        $createData = CreateCampaignData::from([
            'name' => 'The Lost Mines of Phandelver',
            'description' => 'A classic D&D adventure adapted for DaggerHeart',
        ]);

        $result = $this->action->execute($createData, $creator);

        $this->assertInstanceOf(CampaignData::class, $result);
        $this->assertEquals('The Lost Mines of Phandelver', $result->name);
        $this->assertEquals('A classic D&D adventure adapted for DaggerHeart', $result->description);
        $this->assertEquals($creator->id, $result->creator_id);
        $this->assertEquals(CampaignStatus::ACTIVE, $result->status);
    }

    #[Test]
    public function it_auto_generates_invite_code(): void
    {
        $creator = User::factory()->create();
        $createData = CreateCampaignData::from([
            'name' => 'Test Campaign',
            'description' => 'Test Description',
        ]);

        $result = $this->action->execute($createData, $creator);

        $this->assertNotNull($result->invite_code);
        $this->assertEquals(8, strlen($result->invite_code));
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{8}$/', $result->invite_code);
    }

    #[Test]
    public function it_auto_generates_campaign_code(): void
    {
        $creator = User::factory()->create();
        $createData = CreateCampaignData::from([
            'name' => 'Test Campaign',
            'description' => 'Test Description',
        ]);

        $result = $this->action->execute($createData, $creator);

        $this->assertNotNull($result->campaign_code);
        $this->assertEquals(8, strlen($result->campaign_code));
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{8}$/', $result->campaign_code);
    }

    #[Test]
    public function it_ensures_codes_are_different(): void
    {
        $creator = User::factory()->create();
        $createData = CreateCampaignData::from([
            'name' => 'Test Campaign',
            'description' => 'Test Description',
        ]);

        $result = $this->action->execute($createData, $creator);

        $this->assertNotEquals($result->invite_code, $result->campaign_code);
    }

    #[Test]
    public function it_persists_campaign_to_database(): void
    {
        $creator = User::factory()->create();
        $createData = CreateCampaignData::from([
            'name' => 'Persistent Campaign',
            'description' => 'This should be saved to the database',
        ]);

        $result = $this->action->execute($createData, $creator);

        $this->assertDatabaseHas('campaigns', [
            'id' => $result->id,
            'name' => 'Persistent Campaign',
            'description' => 'This should be saved to the database',
            'creator_id' => $creator->id,
            'invite_code' => $result->invite_code,
            'campaign_code' => $result->campaign_code,
            'status' => CampaignStatus::ACTIVE->value,
        ]);
    }

    #[Test]
    public function it_loads_creator_relationship(): void
    {
        $creator = User::factory()->create(['username' => 'dungeon_master']);
        $createData = CreateCampaignData::from([
            'name' => 'Test Campaign',
            'description' => 'Test Description',
        ]);

        $result = $this->action->execute($createData, $creator);

        $this->assertNotNull($result->creator);
        $this->assertEquals('dungeon_master', $result->creator->username);
        $this->assertEquals($creator->id, $result->creator->id);
    }

    #[Test]
    public function it_creates_campaigns_with_unique_codes(): void
    {
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

        $this->assertNotEquals($result1->invite_code, $result2->invite_code);
        $this->assertNotEquals($result1->campaign_code, $result2->campaign_code);
    }

    #[Test]
    public function it_handles_long_names_and_descriptions(): void
    {
        $creator = User::factory()->create();
        $createData = CreateCampaignData::from([
            'name' => str_repeat('A', 100), // Max length
            'description' => str_repeat('B', 1000), // Max length
        ]);

        $result = $this->action->execute($createData, $creator);

        $this->assertEquals(str_repeat('A', 100), $result->name);
        $this->assertEquals(str_repeat('B', 1000), $result->description);
    }

    #[Test]
    public function it_associates_creator_correctly(): void
    {
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

        $this->assertEquals($creator1->id, $result1->creator_id);
        $this->assertEquals($creator2->id, $result2->creator_id);
        $this->assertNotEquals($result1->creator_id, $result2->creator_id);
    }

    #[Test]
    public function it_initializes_member_count_as_null(): void
    {
        $creator = User::factory()->create();
        $createData = CreateCampaignData::from([
            'name' => 'Test Campaign',
            'description' => 'Test Description',
        ]);

        $result = $this->action->execute($createData, $creator);

        // Member count should be null in the result since it's loaded separately
        $this->assertNull($result->member_count);
    }
}
