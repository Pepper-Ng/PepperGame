<?php

namespace OGame\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\View\View;
use OGame\Enums\OfficerType;
use OGame\Facades\AppUtil;
use OGame\Services\PlayerService;
use OGame\Services\PremiumOfficerService;
use RuntimeException;

class PremiumController extends OGameController
{
    /**
     * Shows the premium/officers index page
     *
     * @return View
     */
    public function index(Request $request, PlayerService $playerService, PremiumOfficerService $premiumOfficerService): View
    {
        $this->setBodyId('premium');

        $officers = $this->buildOfficerCatalog($playerService, $premiumOfficerService);

        return view('ingame.premium.index', [
            'darkMatter' => $playerService->getDarkMatter(),
            'officers' => $officers,
            'initialOfficerRef' => $this->resolveInitialOfficerRef($request, $officers),
        ]);
    }

    /**
     * Returns the premium detail panel for the selected officer.
     */
    public function detail(Request $request, PlayerService $playerService, PremiumOfficerService $premiumOfficerService): View
    {
        $officers = $this->buildOfficerCatalog($playerService, $premiumOfficerService);
        $officerRef = (string) $request->query('type', '');

        abort_unless(isset($officers[$officerRef]), 404);

        return view('ingame.premium.detail', [
            'officer' => $officers[$officerRef],
        ]);
    }

    /**
     * Processes an officer hire / extension purchase.
     */
    public function purchase(Request $request, PlayerService $playerService, PremiumOfficerService $premiumOfficerService): RedirectResponse
    {
        $officer = OfficerType::fromPremiumRef((string) $request->input('type', ''));

        abort_unless($officer !== null, 404);

        try {
            $premiumOfficerService->purchase($playerService->getUser(), $officer);
        } catch (RuntimeException $exception) {
            return redirect()->route('premium.index', ['openDetail' => $officer->premiumRef()])
                ->with('error', $exception->getMessage());
        }

        return redirect()->route('premium.index', ['openDetail' => $officer->premiumRef()]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function buildOfficerCatalog(PlayerService $playerService, PremiumOfficerService $premiumOfficerService): array
    {
        $user = $playerService->getUser();

        $officers = [
            '1' => [
                'ref' => '1',
                'title' => __('t_ingame.layout.res_dark_matter'),
                'image_class' => 'darkMatter',
                'description' => __('t_ingame.premium.intro_text'),
                'benefits' => [],
                'meta' => [number_format($playerService->getDarkMatter(), 0, ',', '.') . ' ' . __('t_ingame.shop.dark_matter')],
                'show_payment_overlay' => true,
                'action_label' => __('t_ingame.shop.btn_purchase_dark_matter'),
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
                'meta' => [__('t_ingame.premium.remaining_officers', [
                    'current' => $user->getActiveOfficerCount(),
                    'max' => count(OfficerType::cases()),
                ])],
                'active_count' => $user->getActiveOfficerCount(),
                'max_count' => count(OfficerType::cases()),
                'status' => [
                    'is_active' => $playerService->hasCommandingStaff(),
                ],
                'show_payment_overlay' => false,
                'purchasable' => false,
            ],
        ];

        $now = Date::now();

        foreach ($premiumOfficerService->getOffers() as $offer) {
            $officer = $offer['type'];
            $tooltip = __('t_ingame.premium.' . $officer->tooltipTranslationKey());
            $isActive = $user->isOfficerActive($officer);
            $expiresAt = $user->getOfficerUntil($officer);
            $remainingSeconds = $expiresAt !== null
                ? max(0, $expiresAt->getTimestamp() - $now->getTimestamp())
                : 0;
            $canAfford = $premiumOfficerService->canAfford($user, $officer);
            $meta = [
                AppUtil::formatTimeDuration($offer['duration_seconds']),
                number_format($offer['price'], 0, ',', '.') . ' ' . __('t_ingame.shop.dark_matter'),
            ];

            if ($isActive && $expiresAt !== null) {
                array_unshift($meta, AppUtil::formatTimeDuration($remainingSeconds));
            }

            $officers[$offer['ref']] = [
                'ref' => $offer['ref'],
                'officer_type' => $officer->value,
                'title' => $this->extractOfficerTitle(__('t_ingame.premium.' . $officer->infoTranslationKey())),
                'image_class' => $officer->imageClass(),
                'description' => __('t_ingame.premium.intro_text'),
                'benefits' => $this->extractTooltipBenefits($tooltip),
                'meta' => $meta,
                'offer' => [
                    'price' => $offer['price'],
                    'currency' => 'dark_matter',
                    'duration_seconds' => $offer['duration_seconds'],
                ],
                'status' => [
                    'is_active' => $isActive,
                    'can_afford' => $canAfford,
                    'expires_at' => $expiresAt?->toIso8601String(),
                    'remaining_seconds' => $remainingSeconds,
                ],
                'purchasable' => true,
                'show_payment_overlay' => false,
                'can_afford' => $canAfford,
                'action_label' => $isActive ? __('t_ingame.shop.loca_buy_extend') : $this->extractTooltipAction($tooltip),
            ];
        }

        return $officers;
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

    private function extractTooltipAction(string $tooltip): string
    {
        $parts = explode('|', strip_tags($tooltip), 2);

        return trim($parts[0] ?? '');
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
