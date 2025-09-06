<?php

declare(strict_types=1);

namespace Domain\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserStorageAccount extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'encrypted_credentials' => 'encrypted:json',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this is a Wasabi storage account
     */
    public function isWasabi(): bool
    {
        return $this->provider === 'wasabi';
    }

    /**
     * Check if this is a Google Drive storage account
     */
    public function isGoogleDrive(): bool
    {
        return $this->provider === 'google_drive';
    }

    /**
     * Check if this is an AssemblyAI account
     */
    public function isAssemblyAI(): bool
    {
        return $this->provider === 'assemblyai';
    }

    /**
     * Get the decrypted credentials as an array
     */
    public function getCredentials(): array
    {
        return $this->encrypted_credentials ?? [];
    }

    /**
     * Get a specific credential value
     */
    public function getCredential(string $key): mixed
    {
        return $this->getCredentials()[$key] ?? null;
    }

    /**
     * Set the encrypted credentials
     */
    public function setCredentials(array $credentials): void
    {
        $this->encrypted_credentials = $credentials;
    }

    /**
     * Scope query to active accounts only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query to a specific provider
     */
    public function scopeProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    protected static function newFactory()
    {
        return \Database\Factories\UserStorageAccountFactory::new();
    }
}
