<?php

declare(strict_types=1);

namespace Domain\CampaignFrame\Data;

use Domain\CampaignFrame\Enums\ComplexityRating;
use Livewire\Wireable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class UpdateCampaignFrameData extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        #[Required, Max(100)]
        public string $name,
        
        #[Required, Max(500)]
        public string $description,
        
        #[Required]
        public ComplexityRating $complexity_rating,
        
        public bool $is_public = false,
        
        public array $pitch = [],
        public array $touchstones = [],
        public array $tone = [],
        public array $themes = [],
        public array $player_principles = [],
        public array $gm_principles = [],
        public array $community_guidance = [],
        public array $ancestry_guidance = [],
        public array $class_guidance = [],
        
        #[Max(2000)]
        public string $background_overview = '',
        
        public array $setting_guidance = [],
        public array $setting_distinctions = [],
        
        #[Max(1000)]
        public string $inciting_incident = '',
        
        public array $special_mechanics = [],
        public array $campaign_mechanics = [],
        public array $session_zero_questions = [],
    ) {}
}
