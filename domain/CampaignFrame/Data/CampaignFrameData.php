<?php

declare(strict_types=1);

namespace Domain\CampaignFrame\Data;

use Domain\CampaignFrame\Enums\ComplexityRating;
use Domain\CampaignFrame\Models\CampaignFrame;
use Domain\User\Data\UserData;
use Illuminate\Support\Collection;
use Livewire\Wireable;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class CampaignFrameData extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        public ?int $id,
        public string $name,
        public string $description,
        public ComplexityRating $complexity_rating,
        public bool $is_public,
        public int $creator_id,
        public ?UserData $creator,
        public array $pitch,
        public array $touchstones,
        public array $tone,
        public array $themes,
        public array $player_principles,
        public array $gm_principles,
        public array $community_guidance,
        public array $ancestry_guidance,
        public array $class_guidance,
        public string $background_overview,
        public array $setting_guidance,
        public array $setting_distinctions,
        public string $inciting_incident,
        public array $special_mechanics,
        public array $campaign_mechanics,
        public array $session_zero_questions,
        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {}

    public static function fromModel(CampaignFrame $frame): self
    {
        return self::from([
            'id' => $frame->id,
            'name' => $frame->name,
            'description' => $frame->description,
            'complexity_rating' => $frame->complexity_rating,
            'is_public' => $frame->is_public,
            'creator_id' => $frame->creator_id,
            'creator' => $frame->creator ? UserData::from($frame->creator) : null,
            'pitch' => $frame->pitch ?? [],
            'touchstones' => $frame->touchstones ?? [],
            'tone' => $frame->tone ?? [],
            'themes' => $frame->themes ?? [],
            'player_principles' => $frame->player_principles ?? [],
            'gm_principles' => $frame->gm_principles ?? [],
            'community_guidance' => $frame->community_guidance ?? [],
            'ancestry_guidance' => $frame->ancestry_guidance ?? [],
            'class_guidance' => $frame->class_guidance ?? [],
            'background_overview' => $frame->background_overview ?? '',
            'setting_guidance' => $frame->setting_guidance ?? [],
            'setting_distinctions' => $frame->setting_distinctions ?? [],
            'inciting_incident' => $frame->inciting_incident ?? '',
            'special_mechanics' => $frame->special_mechanics ?? [],
            'campaign_mechanics' => $frame->campaign_mechanics ?? [],
            'session_zero_questions' => $frame->session_zero_questions ?? [],
            'created_at' => $frame->created_at?->toISOString(),
            'updated_at' => $frame->updated_at?->toISOString(),
        ]);
    }

    /**
     * @return Collection<CampaignFrameData>
     */
    public static function collectionFromModels(Collection $frames): Collection
    {
        return $frames->map(fn (CampaignFrame $frame) => self::fromModel($frame));
    }
}
