<?php

namespace OGame\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PremiumController extends OGameController
{
    /**
     * Shows the premium/officers index page
     *
     * @return View
     */
    public function index(Request $request): View
    {
        $this->setBodyId('premium');

        // Get current user's dark matter balance
        $darkMatter = Auth::user()->dark_matter ?? 0;
        $officers = $this->getOfficerCatalog();

        return view('ingame.premium.index', [
            'darkMatter' => $darkMatter,
            'initialOfficerRef' => $this->resolveInitialOfficerRef($request, $officers),
        ]);
    }

    /**
     * Returns the premium detail panel for the selected officer.
     */
    public function detail(Request $request): View
    {
        $officers = $this->getOfficerCatalog();
        $officerRef = (string) $request->query('type', '');

        abort_unless(isset($officers[$officerRef]), 404);

        return view('ingame.premium.detail', [
            'officer' => $officers[$officerRef],
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function getOfficerCatalog(): array
    {
        return [
            '1' => [
                'ref' => '1',
                'title' => __('t_ingame.layout.res_dark_matter'),
                'image_class' => 'darkMatter',
                'description' => __('t_ingame.premium.intro_text'),
                'benefits' => [],
            ],
            '2' => [
                'ref' => '2',
                'title' => $this->extractOfficerTitle(__('t_ingame.premium.info_commander')),
                'image_class' => 'commander',
                'description' => __('t_ingame.premium.intro_text'),
                'benefits' => $this->extractTooltipBenefits(__('t_ingame.premium.hire_commander_tooltip')),
            ],
            '3' => [
                'ref' => '3',
                'title' => $this->extractOfficerTitle(__('t_ingame.premium.info_admiral')),
                'image_class' => 'admiral',
                'description' => __('t_ingame.premium.intro_text'),
                'benefits' => $this->extractTooltipBenefits(__('t_ingame.premium.hire_admiral_tooltip')),
            ],
            '4' => [
                'ref' => '4',
                'title' => $this->extractOfficerTitle(__('t_ingame.premium.info_engineer')),
                'image_class' => 'engineer',
                'description' => __('t_ingame.premium.intro_text'),
                'benefits' => $this->extractTooltipBenefits(__('t_ingame.premium.hire_engineer_tooltip')),
            ],
            '5' => [
                'ref' => '5',
                'title' => $this->extractOfficerTitle(__('t_ingame.premium.info_geologist')),
                'image_class' => 'geologist',
                'description' => __('t_ingame.premium.intro_text'),
                'benefits' => $this->extractTooltipBenefits(__('t_ingame.premium.hire_geologist_tooltip')),
            ],
            '6' => [
                'ref' => '6',
                'title' => $this->extractOfficerTitle(__('t_ingame.premium.info_technocrat')),
                'image_class' => 'technocrat',
                'description' => __('t_ingame.premium.intro_text'),
                'benefits' => $this->extractTooltipBenefits(__('t_ingame.premium.hire_technocrat_tooltip')),
            ],
            '12' => [
                'ref' => '12',
                'title' => $this->extractOfficerTitle(__('t_ingame.premium.info_commanding_staff')),
                'image_class' => 'allOfficers',
                'description' => __('t_ingame.premium.intro_text'),
                'benefits' => [
                    __('t_ingame.premium.benefit_fleet_slots'),
                    __('t_ingame.premium.benefit_energy'),
                    __('t_ingame.premium.benefit_mines'),
                    __('t_ingame.premium.benefit_espionage'),
                ],
            ],
        ];
    }

    /**
     * @param array<string, array<string, mixed>> $officers
     */
    private function resolveInitialOfficerRef(Request $request, array $officers): ?string
    {
        if ($request->boolean('showDarkMatter') && isset($officers['1'])) {
            return '1';
        }

        $openDetail = (string) $request->query('openDetail', '');

        return isset($officers[$openDetail]) ? $openDetail : null;
    }

    private function extractOfficerTitle(string $title): string
    {
        $parts = explode(':', $title, 2);

        return trim(end($parts));
    }

    /**
     * @return array<int, string>
     */
    private function extractTooltipBenefits(string $tooltip): array
    {
        $normalizedTooltip = str_replace(["\r\n", "\n"], ',', strip_tags($tooltip));
        $parts = explode('|', $normalizedTooltip, 2);
        $benefits = $parts[1] ?? '';

        return array_values(array_filter(array_map(
            static fn (string $benefit): string => trim($benefit),
            preg_split('/\s*,\s*/', $benefits, -1, PREG_SPLIT_NO_EMPTY) ?: []
        )));
    }
}
