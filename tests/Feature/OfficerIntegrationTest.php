<?php

namespace Tests\Feature;

use OGame\Models\Planet;
use OGame\Services\OfficerService;
use OGame\Services\PlanetListService;
use OGame\Services\PlanetService;
use OGame\Services\PlayerService;
use OGame\Services\PlayerServiceFactory;
use RuntimeException;
use Tests\AccountTestCase;

class OfficerIntegrationTest extends AccountTestCase
{
    private OfficerService $officerService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->officerService = resolve(OfficerService::class);
    }

    public function testGeologistPurchaseRefreshesCachedProductionTotals(): void
    {
        $user = $this->playerService->getUser();
        $user->dark_matter = 100000;
        $user->save();

        $this->planetSetObjectLevel('metal_mine', 12);
        $this->planetSetObjectLevel('crystal_mine', 10);
        $this->planetSetObjectLevel('deuterium_synthesizer', 8);
        $this->planetSetObjectLevel('solar_plant', 30);

        $planet = Planet::query()->findOrFail($this->planetService->getPlanetId());

        $metalBeforePurchase = $planet->metal_production;
        $crystalBeforePurchase = $planet->crystal_production;
        $deuteriumBeforePurchase = $planet->deuterium_production;

        $this->officerService->purchase($user, 'geologist', 7);

        $planet->refresh();

        $this->assertGreaterThan($metalBeforePurchase, $planet->metal_production);
        $this->assertGreaterThan($crystalBeforePurchase, $planet->crystal_production);
        $this->assertGreaterThan($deuteriumBeforePurchase, $planet->deuterium_production);
    }

    public function testOfficerPurchaseStillSucceedsWhenProductionRefreshFails(): void
    {
        $user = $this->playerService->getUser();
        $user->dark_matter = 100000;
        $user->save();

        $throwingPlanet = \Mockery::mock(PlanetService::class);
        $throwingPlanet->shouldReceive('updateResourceProductionStats')
            ->once()
            ->andThrow(new RuntimeException('production refresh failed'));

        $healthyPlanet = \Mockery::mock(PlanetService::class);
        $healthyPlanet->shouldReceive('updateResourceProductionStats')
            ->once();

        $throwingPlanetList = \Mockery::mock(PlanetListService::class);
        $throwingPlanetList->shouldReceive('allPlanets')
            ->once()
            ->andReturn([$throwingPlanet, $healthyPlanet]);

        $throwingPlayerService = new PlayerService();
        $throwingPlayerService->planets = $throwingPlanetList;

        $this->partialMock(PlayerServiceFactory::class, function ($mock) use ($throwingPlayerService, $user): void {
            $mock->shouldReceive('make')
                ->once()
                ->with($user->id, true)
                ->andReturn($throwingPlayerService);
        });

        $this->officerService->purchase($user, 'geologist', 7);

        $user->refresh();

        $this->assertSame(87500, $user->dark_matter);
        $this->assertTrue($this->officerService->isActive($user, 'geologist'));
    }

    public function testTechnocratDoesNotGrantBaseEspionageCapabilityInGalaxyState(): void
    {
        $user = $this->playerService->getUser();
        $user->dark_matter = 100000;
        $user->save();

        $this->planetAddUnit('espionage_probe', 1);

        $this->officerService->purchase($user, 'technocrat', 7);

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
}