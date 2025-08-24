<?php

namespace Domain\Character\Enums;

enum ClassEnum: string
{
    case ASSASSIN = 'assassin';
    case BARD = 'bard';
    case BRAWLER = 'brawler';
    case DRUID = 'druid';
    case GUARDIAN = 'guardian';
    case RANGER = 'ranger';
    case ROGUE = 'rogue';
    case SERAPH = 'seraph';
    case SORCERER = 'sorcerer';
    case WARLOCK = 'warlock';
    case WARRIOR = 'warrior';
    case WITCH = 'witch';
    case WIZARD = 'wizard';
    
    public function getDomains(): array
    {
        return match ($this) {
            self::ASSASSIN => [DomainEnum::MIDNIGHT, DomainEnum::BLADE],
            self::BARD => [DomainEnum::GRACE, DomainEnum::CODEX],
            self::BRAWLER => [DomainEnum::BONE, DomainEnum::VALOR],
            self::DRUID => [DomainEnum::SAGE, DomainEnum::ARCANA],
            self::GUARDIAN => [DomainEnum::VALOR, DomainEnum::BLADE],
            self::RANGER => [DomainEnum::BONE, DomainEnum::SAGE],
            self::ROGUE => [DomainEnum::MIDNIGHT, DomainEnum::GRACE],
            self::SERAPH => [DomainEnum::SPLENDOR, DomainEnum::VALOR],
            self::SORCERER => [DomainEnum::ARCANA, DomainEnum::MIDNIGHT],
            self::WARLOCK => [DomainEnum::DREAD, DomainEnum::GRACE],
            self::WARRIOR => [DomainEnum::BLADE, DomainEnum::BONE],
            self::WITCH => [DomainEnum::DREAD, DomainEnum::SAGE],
            self::WIZARD => [DomainEnum::CODEX, DomainEnum::SPLENDOR]
        };
    }
}