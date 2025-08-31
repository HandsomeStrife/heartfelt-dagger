<?php

declare(strict_types=1);

namespace Domain\Campaign\Models;

use Domain\Character\Models\Character;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignMember extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\CampaignMemberFactory::new();
    }

    protected $fillable = [
        'campaign_id',
        'user_id',
        'character_id',
        'joined_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    /**
     * Get the campaign this member belongs to
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Get the user who is this member
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the character this member is using (nullable)
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Check if this member has a character assigned
     */
    public function hasCharacter(): bool
    {
        return !is_null($this->character_id);
    }

    /**
     * Get display name for this member (character name or "Empty Character")
     */
    public function getDisplayName(): string
    {
        if ($this->hasCharacter() && $this->character) {
            return $this->character->name;
        }

        return 'Empty Character';
    }

    /**
     * Get character class for display
     */
    public function getCharacterClass(): ?string
    {
        return $this->hasCharacter() && $this->character ? $this->character->class : null;
    }

    /**
     * Get character subclass for display
     */
    public function getCharacterSubclass(): ?string
    {
        return $this->hasCharacter() && $this->character ? $this->character->subclass : null;
    }

    /**
     * Get character ancestry for display
     */
    public function getCharacterAncestry(): ?string
    {
        return $this->hasCharacter() && $this->character ? $this->character->ancestry : null;
    }

    /**
     * Get character community for display
     */
    public function getCharacterCommunity(): ?string
    {
        return $this->hasCharacter() && $this->character ? $this->character->community : null;
    }

    /**
     * Scope for members with characters
     */
    public function scopeWithCharacters($query)
    {
        return $query->whereNotNull('character_id');
    }

    /**
     * Scope for members without characters (empty slots)
     */
    public function scopeWithoutCharacters($query)
    {
        return $query->whereNull('character_id');
    }
}
