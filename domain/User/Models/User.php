<?php

declare(strict_types=1);

namespace Domain\User\Models;

use Domain\Campaign\Models\Campaign;
use Domain\Campaign\Models\CampaignMember;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\Character\Models\Character;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\UserFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get all characters belonging to this user.
     */
    public function characters(): HasMany
    {
        return $this->hasMany(Character::class);
    }

    /**
     * Get all campaigns created by this user.
     */
    public function createdCampaigns(): HasMany
    {
        return $this->hasMany(Campaign::class, 'creator_id');
    }

    /**
     * Get all campaign memberships for this user.
     */
    public function campaignMemberships(): HasMany
    {
        return $this->hasMany(CampaignMember::class);
    }

    /**
     * Get all campaigns this user has joined (as a member).
     */
    public function joinedCampaigns()
    {
        return $this->belongsToMany(Campaign::class, 'campaign_members')
            ->withPivot(['character_id', 'joined_at'])
            ->withTimestamps();
    }

    /**
     * Get all rooms this user has created.
     */
    public function createdRooms(): HasMany
    {
        return $this->hasMany(Room::class, 'creator_id');
    }

    /**
     * Get all rooms this user has access to (both created and joined).
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class, 'creator_id');
    }

    /**
     * Get all room participations for this user.
     */
    public function roomParticipations(): HasMany
    {
        return $this->hasMany(RoomParticipant::class);
    }

    /**
     * Get all storage accounts for this user.
     */
    public function storageAccounts(): HasMany
    {
        return $this->hasMany(UserStorageAccount::class);
    }

    /**
     * Get all recordings by this user.
     */
    public function recordings(): HasMany
    {
        return $this->hasMany(\Domain\Room\Models\RoomRecording::class);
    }

    /**
     * Get all transcripts by this user.
     */
    public function transcripts(): HasMany
    {
        return $this->hasMany(\Domain\Room\Models\RoomTranscript::class);
    }

    /**
     * Get all rooms this user has joined (as a participant).
     */
    public function joinedRooms()
    {
        return $this->belongsToMany(Room::class, 'room_participants')
            ->withPivot(['character_id', 'character_name', 'character_class', 'joined_at', 'left_at'])
            ->withTimestamps();
    }

    // Relationship to get rooms for campaigns the user is a member of
    public function accessibleRooms()
    {
        return Room::whereHas('campaign', function ($query) {
            $query->whereHas('members', function ($memberQuery) {
                $memberQuery->where('user_id', $this->id);
            });
        })->orWhere('creator_id', $this->id);
    }
}
