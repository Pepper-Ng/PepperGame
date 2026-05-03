<?php

namespace Tests\Feature;

use Tests\MoonTestCase;

class IpiDirectivesTest extends MoonTestCase
{
    public function testIpiMenuContentRouteRenders(): void
    {
        $response = $this->get(route('ipi.menu-content'));

        $response->assertStatus(200);
        $response->assertSee('ipimenucontent');
        $response->assertSee('Directives');
    }

    public function testIpiOverviewRouteRenders(): void
    {
        $response = $this->get(route('ipi.overview.layer'));

        $response->assertStatus(200);
        $response->assertSee('ipioverviewlayer');
        $response->assertSee('ipiTaskItem');
    }

    public function testIpiTrackTaskReturnsParsableJsonPayload(): void
    {
        $response = $this->get(route('ipi.track-task', ['taskId' => 5002]));

        $response->assertStatus(200);
        $response->assertSee('"success":true', false);
        $response->assertSee('trackedAction', false);
    }
}
