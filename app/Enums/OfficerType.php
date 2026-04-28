<?php

namespace OGame\Enums;

enum OfficerType: string
{
    case COMMANDER = 'commander';
    case ADMIRAL = 'admiral';
    case ENGINEER = 'engineer';
    case GEOLOGIST = 'geologist';
    case TECHNOCRAT = 'technocrat';

    public static function fromPremiumRef(string $premiumRef): ?self
    {
        return match ($premiumRef) {
            '2' => self::COMMANDER,
            '3' => self::ADMIRAL,
            '4' => self::ENGINEER,
            '5' => self::GEOLOGIST,
            '6' => self::TECHNOCRAT,
            default => null,
        };
    }

    public function premiumRef(): string
    {
        return match ($this) {
            self::COMMANDER => '2',
            self::ADMIRAL => '3',
            self::ENGINEER => '4',
            self::GEOLOGIST => '5',
            self::TECHNOCRAT => '6',
        };
    }

    public function expiryColumn(): string
    {
        return match ($this) {
            self::COMMANDER => 'commander_until',
            self::ADMIRAL => 'admiral_until',
            self::ENGINEER => 'engineer_until',
            self::GEOLOGIST => 'geologist_until',
            self::TECHNOCRAT => 'technocrat_until',
        };
    }

    public function imageClass(): string
    {
        return $this->value;
    }

    public function infoTranslationKey(): string
    {
        return 'info_' . $this->value;
    }

    public function tooltipTranslationKey(): string
    {
        return 'hire_' . $this->value . '_tooltip';
    }

    public function transactionType(): DarkMatterTransactionType
    {
        return match ($this) {
            self::COMMANDER => DarkMatterTransactionType::COMMANDER,
            self::ADMIRAL => DarkMatterTransactionType::ADMIRAL,
            self::ENGINEER => DarkMatterTransactionType::ENGINEER,
            self::GEOLOGIST => DarkMatterTransactionType::GEOLOGIST,
            self::TECHNOCRAT => DarkMatterTransactionType::TECHNOCRAT,
        };
    }

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
