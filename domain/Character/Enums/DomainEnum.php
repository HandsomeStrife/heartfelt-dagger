<?php

namespace Domain\Character\Enums;

enum DomainEnum: string
{
    case VALOR = 'valor';
    case SPLENDOR = 'splendor';
    case SAGE = 'sage';
    case MIDNIGHT = 'midnight';
    case GRACE = 'grace';
    case CODEX = 'codex';
    case BONE = 'bone';
    case BLADE = 'blade';
    case ARCANA = 'arcana';
    case DREAD = 'dread';

    public function getColor(): string
    {
        return match ($this) {
            self::VALOR => '#e2680e',
            self::SPLENDOR => '#b8a342',
            self::SAGE => '#244e30',
            self::MIDNIGHT => '#1e201f',
            self::GRACE => '#8d3965',
            self::CODEX => '#24395d',
            self::BONE => '#a4a9a8',
            self::BLADE => '#af231c',
            self::ARCANA => '#4e345b',
            self::DREAD => '#362b62',
        };
    }

    public function getName(): string
    {
        return match ($this) {
            self::VALOR => 'Valor',
            self::SPLENDOR => 'Splendor',
            self::SAGE => 'Sage',
            self::MIDNIGHT => 'Midnight',
            self::GRACE => 'Grace',
            self::CODEX => 'Codex',
            self::BONE => 'Bone',
            self::BLADE => 'Blade',
            self::ARCANA => 'Arcana',
            self::DREAD => 'Dread',
        };
    }
}