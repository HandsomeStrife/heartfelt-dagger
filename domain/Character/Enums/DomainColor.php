<?php

declare(strict_types=1);

namespace Domain\Character\Enums;

/**
 * Domain color definitions for consistent theming across UI
 * 
 * Each of the 9 magical domains has a specific color for visual identification
 */
enum DomainColor: string
{
    case ARCANA = '#4e345b';
    case BLADE = '#af231c';
    case BONE = '#a4a9a8';
    case CODEX = '#24395d';
    case GRACE = '#8d3965';
    case MIDNIGHT = '#1e201f';
    case SAGE = '#244e30';
    case SPLENDOR = '#b8a342';
    case VALOR = '#e2680e';

    /**
     * Get the color for a given domain key
     *
     * @param string $domain The domain key (e.g., 'arcana', 'blade')
     * @return string The hex color code
     */
    public static function fromDomain(string $domain): string
    {
        return match (strtolower($domain)) {
            'arcana' => self::ARCANA->value,
            'blade' => self::BLADE->value,
            'bone' => self::BONE->value,
            'codex' => self::CODEX->value,
            'grace' => self::GRACE->value,
            'midnight' => self::MIDNIGHT->value,
            'sage' => self::SAGE->value,
            'splendor' => self::SPLENDOR->value,
            'valor' => self::VALOR->value,
            default => '#64748b', // Default slate-500 for unknown domains
        };
    }

    /**
     * Get all domain colors as an associative array
     *
     * @return array<string, string>
     */
    public static function all(): array
    {
        return [
            'arcana' => self::ARCANA->value,
            'blade' => self::BLADE->value,
            'bone' => self::BONE->value,
            'codex' => self::CODEX->value,
            'grace' => self::GRACE->value,
            'midnight' => self::MIDNIGHT->value,
            'sage' => self::SAGE->value,
            'splendor' => self::SPLENDOR->value,
            'valor' => self::VALOR->value,
        ];
    }

    /**
     * Get the domain name with proper capitalization
     *
     * @return string
     */
    public function getDisplayName(): string
    {
        return match ($this) {
            self::ARCANA => 'Arcana',
            self::BLADE => 'Blade',
            self::BONE => 'Bone',
            self::CODEX => 'Codex',
            self::GRACE => 'Grace',
            self::MIDNIGHT => 'Midnight',
            self::SAGE => 'Sage',
            self::SPLENDOR => 'Splendor',
            self::VALOR => 'Valor',
        };
    }
}


