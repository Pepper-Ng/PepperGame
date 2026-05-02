<?php

namespace OGame\Enums;

/**
 * Alliance Class — set by the alliance founder/leader, applies to all members.
 *
 * Source: official OGame (s274-it.ogame.gameforge.com), "Alliance Class" tab.
 * Verified bonuses (official Italian text):
 *
 *   id=1 Warrior (Alliance)
 *     - Ship speed for flights between alliance members: +10%
 *     - Military research: +1 level
 *     - Espionage research: +1 level
 *     - System espionage can scan the whole system.
 *
 *   id=2 Trader (Alliance)
 *     - Cargo speed: +10%
 *     - Mine production: +5%
 *     - Energy production: +5%
 *     - Planet storage capacity: +10%
 *     - Moon storage capacity: +10%
 *
 *   id=3 Researcher (Alliance)
 *     - Larger planets (+5%) when colonizing
 *     - Flight speed to expedition destination: +10%
 *     - System phalanx can scan fleet movements across the whole system.
 *
 * Activation rules (verified on official UI):
 *   - Cost per change: 500.000 MO
 *   - First activation FREE, but only after 14 days from alliance creation
 *     ("Activate for free: You must wait until ..." tooltip)
 *   - Bonus applies to ALL members of the alliance
 *   - Bonus is permanent until class is changed
 */
enum AllianceClass: int
{
    case WARRIOR = 1;
    case TRADER = 2;
    case RESEARCHER = 3;

    public function getName(): string
    {
        return match ($this) {
            self::WARRIOR => __('t_ingame.alliance.class_warrior'),
            self::TRADER => __('t_ingame.alliance.class_trader'),
            self::RESEARCHER => __('t_ingame.alliance.class_researcher'),
        };
    }

    /**
     * CSS sprite class name.
     */
    public function getMachineName(): string
    {
        return match ($this) {
            self::WARRIOR => 'warrior',
            self::TRADER => 'trader',
            self::RESEARCHER => 'explorer',
        };
    }

    public function getChangeCost(): int
    {
        return 500000;
    }

    /**
     * Days an alliance must exist before the founder can activate the
     * first class for free. Verified on official UI tooltip.
     */
    public static function getFreeActivationDelayDays(): int
    {
        return 14;
    }

    /**
     * Localised lang keys (lookup in t_ingame.alliance) for bonus list.
     *
     * @return array<int, string>
     */
    public function getBonusLangKeys(): array
    {
        return match ($this) {
            self::WARRIOR => [
                't_ingame.alliance.warrior_bonus_1',
                't_ingame.alliance.warrior_bonus_2',
                't_ingame.alliance.warrior_bonus_3',
                't_ingame.alliance.warrior_bonus_4',
            ],
            self::TRADER => [
                't_ingame.alliance.trader_bonus_1',
                't_ingame.alliance.trader_bonus_2',
                't_ingame.alliance.trader_bonus_3',
                't_ingame.alliance.trader_bonus_4',
                't_ingame.alliance.trader_bonus_5',
            ],
            self::RESEARCHER => [
                't_ingame.alliance.researcher_bonus_1',
                't_ingame.alliance.researcher_bonus_2',
                't_ingame.alliance.researcher_bonus_3',
            ],
        };
    }
}
