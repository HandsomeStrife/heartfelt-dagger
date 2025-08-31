<?php

declare(strict_types=1);

namespace Domain\Room\Models;

use Domain\Character\Models\Character;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class RoomParticipant extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
        'stt_consent_given' => 'boolean',
        'stt_consent_at' => 'datetime',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Check if this participant has a character (vs temporary character info)
     */
    public function hasCharacter(): bool
    {
        return !is_null($this->character_id);
    }

    /**
     * Get the display name for this participant
     */
    public function getDisplayName(): string
    {
        if ($this->hasCharacter()) {
            return $this->character->name;
        }

        return $this->character_name ?? $this->user->username;
    }

    /**
     * Get the character class for display
     */
    public function getCharacterClass(): ?string
    {
        if ($this->hasCharacter()) {
            return $this->character->class;
        }

        return $this->character_class;
    }

    /**
     * Get the character subclass for display (only for linked characters)
     */
    public function getCharacterSubclass(): ?string
    {
        if ($this->hasCharacter()) {
            return $this->character->subclass;
        }

        return null;
    }

    /**
     * Get the character ancestry for display (only for linked characters)
     */
    public function getCharacterAncestry(): ?string
    {
        if ($this->hasCharacter()) {
            return $this->character->ancestry;
        }

        return null;
    }

    /**
     * Get the character community for display (only for linked characters)
     */
    public function getCharacterCommunity(): ?string
    {
        if ($this->hasCharacter()) {
            return $this->character->community;
        }

        return null;
    }

    /**
     * Check if this participant is currently active (hasn't left)
     */
    public function isActive(): bool
    {
        return is_null($this->left_at);
    }

    /**
     * Check if this participant has given consent for speech-to-text
     */
    public function hasSttConsent(): bool
    {
        return $this->stt_consent_given === true;
    }

    /**
     * Check if this participant has explicitly denied STT consent
     */
    public function hasDeniedSttConsent(): bool
    {
        return $this->stt_consent_given === false;
    }

    /**
     * Check if this participant hasn't made a consent decision yet
     */
    public function hasNoSttConsentDecision(): bool
    {
        return is_null($this->stt_consent_given);
    }

    /**
     * Grant STT consent for this participant
     */
    public function grantSttConsent(): void
    {
        $this->update([
            'stt_consent_given' => true,
            'stt_consent_at' => now(),
        ]);
    }

    /**
     * Deny STT consent for this participant
     */
    public function denySttConsent(): void
    {
        $this->update([
            'stt_consent_given' => false,
            'stt_consent_at' => now(),
        ]);
    }

    /**
     * Reset STT consent decision (remove consent decision)
     */
    public function resetSttConsent(): void
    {
        $this->update([
            'stt_consent_given' => null,
            'stt_consent_at' => null,
        ]);
    }

    /**
     * Scope a query to only include active participants
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('left_at');
    }

    /**
     * Scope a query to only include participants with characters
     */
    public function scopeWithCharacters(Builder $query): Builder
    {
        return $query->whereNotNull('character_id');
    }

    /**
     * Scope a query to only include participants without characters (temporary)
     */
    public function scopeWithoutCharacters(Builder $query): Builder
    {
        return $query->whereNull('character_id');
    }

    /**
     * Scope a query to only include participants who have given STT consent
     */
    public function scopeWithSttConsent(Builder $query): Builder
    {
        return $query->where('stt_consent_given', true);
    }

    /**
     * Scope a query to only include participants who have denied STT consent
     */
    public function scopeWithoutSttConsent(Builder $query): Builder
    {
        return $query->where('stt_consent_given', false);
    }

    /**
     * Scope a query to only include participants with no STT consent decision
     */
    public function scopePendingSttConsent(Builder $query): Builder
    {
        return $query->whereNull('stt_consent_given');
    }

    protected static function newFactory()
    {
        return \Database\Factories\RoomParticipantFactory::new();
    }
}
