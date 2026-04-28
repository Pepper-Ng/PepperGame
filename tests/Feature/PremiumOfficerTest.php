<?php

namespace Tests\Feature;

use OGame\Enums\DarkMatterTransactionType;
use OGame\Enums\OfficerType;
use OGame\Services\SettingsService;
use Tests\AccountTestCase;

class PremiumOfficerTest extends AccountTestCase
{
    public function testOfficerPurchaseDeductsDarkMatterAndPersistsExpiry(): void
    {
        $user = $this->playerService->getUser();
        $user->dark_matter = 10000;
        $user->save();

        $response = $this->post('/premium/purchase', [
            'type' => OfficerType::TECHNOCRAT->premiumRef(),
        ]);

        $response->assertRedirect(route('premium.index', ['openDetail' => OfficerType::TECHNOCRAT->premiumRef()]));

        $user->refresh();

        $this->assertSame(6500, $user->dark_matter);
        $this->assertNotNull($user->technocrat_until);
        $this->assertTrue($user->isOfficerActive(OfficerType::TECHNOCRAT));
        $this->assertSame(1, $user->getActiveOfficerCount());

        $this->assertDatabaseHas('dark_matter_transactions', [
            'user_id' => $user->id,
            'amount' => -3500,
            'type' => DarkMatterTransactionType::TECHNOCRAT->value,
            'balance_after' => 6500,
        ]);
    }

    public function testOfficerPurchaseExtendsExistingExpiry(): void
    {
        $user = $this->playerService->getUser();
        $user->dark_matter = 10000;
        $user->technocrat_until = now()->addDays(3);
        $user->save();

        $existingExpiry = $user->technocrat_until->copy();

        $this->post('/premium/purchase', [
            'type' => OfficerType::TECHNOCRAT->premiumRef(),
        ]);

        $user->refresh();

        $this->assertTrue($user->technocrat_until->equalTo($existingExpiry->addWeek()));
    }

    public function testOfficerPurchaseRequiresEnoughDarkMatter(): void
    {
        $user = $this->playerService->getUser();
        $user->dark_matter = 100;
        $user->save();

        $response = $this->post('/premium/purchase', [
            'type' => OfficerType::ENGINEER->premiumRef(),
        ]);

        $response->assertRedirect(route('premium.index', ['openDetail' => OfficerType::ENGINEER->premiumRef()]));
        $response->assertSessionHas('error');

        $user->refresh();

        $this->assertSame(100, $user->dark_matter);
        $this->assertNull($user->engineer_until);
    }

    public function testTechnocratDoesNotGrantBaseEspionageCapabilityInGalaxyState(): void
    {
        $this->planetAddUnit('espionage_probe', 1);

        $this->activateOfficers(OfficerType::TECHNOCRAT);

        $this->assertSame(2, $this->playerService->getEffectiveResearchLevel('espionage_technology'));
        $this->assertFalse($this->playerService->hasEspionageCapability());

        $coordinates = $this->planetService->getPlanetCoordinates();

        $response = $this->post('/ajax/galaxy', [
            'galaxy' => $coordinates->galaxy,
            'system' => $coordinates->system,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('system.canSystemEspionage', false);
    }

    public function testOfficerBonusesAffectPlayerState(): void
    {
        $this->planetSetObjectLevel('research_lab', 10);

        $baseFleetSlots = $this->playerService->getFleetSlotsMax();
        $baseExpeditionSlots = $this->playerService->getExpeditionSlotsMax();
        $baseResearchTime = (int) $this->planetService->getTechnologyResearchTime('energy_technology');

        $this->activateOfficers(OfficerType::ADMIRAL, OfficerType::TECHNOCRAT);

        $this->assertSame(2, $this->playerService->getEffectiveResearchLevel('espionage_technology'));
        $this->assertSame($baseFleetSlots + 2, $this->playerService->getFleetSlotsMax());
        $this->assertSame($baseExpeditionSlots + 1, $this->playerService->getExpeditionSlotsMax());
        $this->assertSame((int) ($baseResearchTime * 0.75), (int) $this->planetService->getTechnologyResearchTime('energy_technology'));
    }

    public function testCommandingStaffAddsAggregateFleetSlotAndEspionageBonuses(): void
    {
        $this->playerSetResearchLevel('espionage_technology', 1);

        $baseFleetSlots = $this->playerService->getFleetSlotsMax();

        $this->activateOfficers(...OfficerType::cases());

        $this->assertTrue($this->playerService->hasCommandingStaff());
        $this->assertSame(4, $this->playerService->getEffectiveResearchLevel('espionage_technology'));
        $this->assertSame($baseFleetSlots + 3, $this->playerService->getFleetSlotsMax());
    }

    public function testPremiumCatalogExposesSettingsBackedOfferContract(): void
    {
        $settingsService = resolve(SettingsService::class);
        $settingsService->set('premium_technocrat_price', 4200);
        $settingsService->set('premium_technocrat_duration_seconds', 86400);

        $user = $this->playerService->getUser();
        $user->dark_matter = 0;
        $user->save();

        $this->activateOfficers(OfficerType::TECHNOCRAT);

        $response = $this->get('/premium');

        $response->assertOk();
        $response->assertViewHas('officers', function (array $officers): bool {
            $technocrat = $officers[OfficerType::TECHNOCRAT->premiumRef()] ?? null;

            return is_array($technocrat)
                && ($technocrat['officer_type'] ?? null) === OfficerType::TECHNOCRAT->value
                && ($technocrat['offer']['price'] ?? null) === 4200
                && ($technocrat['offer']['currency'] ?? null) === 'dark_matter'
                && ($technocrat['offer']['duration_seconds'] ?? null) === 86400
                && ($technocrat['status']['is_active'] ?? null) === true
                && is_string($technocrat['status']['expires_at'] ?? null)
                && ($technocrat['status']['remaining_seconds'] ?? 0) > 0
                && ($technocrat['status']['can_afford'] ?? null) === false;
        });
    }

    private function activateOfficers(OfficerType ...$officers): void
    {
        $user = $this->playerService->getUser();
        $expiresAt = now()->addWeek();

        foreach ($officers as $officer) {
            $user->setOfficerUntil($officer, $expiresAt);
        }

        $user->save();
    }
}
