<?php

namespace OGame\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use OGame\Enums\OfficerType;
use OGame\Models\User;
use RuntimeException;

class PremiumOfficerService
{
    private const DEFAULT_PRICE = 3500;
    private const DEFAULT_DURATION_SECONDS = 604800;

    public function __construct(
        private readonly SettingsService $settingsService,
        private readonly DarkMatterTransactionService $darkMatterTransactionService,
    ) {
    }

    /**
     * @return array<string, array{ref: string, type: OfficerType, price: int, duration_seconds: int}>
     */
    public function getOffers(): array
    {
        $offers = [];

        foreach (OfficerType::cases() as $officer) {
            $offers[$officer->premiumRef()] = [
                'ref' => $officer->premiumRef(),
                'type' => $officer,
                'price' => $this->resolvePrice($officer),
                'duration_seconds' => $this->resolveDurationSeconds($officer),
            ];
        }

        return $offers;
    }

    /**
     * @return array{ref: string, type: OfficerType, price: int, duration_seconds: int}
     */
    public function getOffer(OfficerType $officer): array
    {
        return $this->getOffers()[$officer->premiumRef()];
    }

    /**
     * @return array{ref: string, type: OfficerType, price: int, duration_seconds: int}|null
     */
    public function findOfferByPremiumRef(string $premiumRef): ?array
    {
        $offers = $this->getOffers();

        return $offers[$premiumRef] ?? null;
    }

    public function canAfford(User $user, OfficerType $officer): bool
    {
        return $user->dark_matter >= $this->getOffer($officer)['price'];
    }

    /**
     * @return array{expires_at: Carbon, balance_after: int}
     */
    public function purchase(User $user, OfficerType $officer): array
    {
        $offer = $this->getOffer($officer);

        $result = DB::transaction(function () use ($user, $officer, $offer): array {
            $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();

            if ($lockedUser->dark_matter < $offer['price']) {
                throw new RuntimeException('Not enough Dark Matter to hire this officer.');
            }

            $now = Date::now();
            $currentExpiry = $lockedUser->getOfficerUntil($officer);
            $baseTime = $currentExpiry !== null && $currentExpiry->greaterThan($now)
                ? $currentExpiry->copy()
                : $now;
            $newExpiry = $baseTime->copy()->addSeconds($offer['duration_seconds']);

            $lockedUser->dark_matter -= $offer['price'];
            $lockedUser->setOfficerUntil($officer, $newExpiry);
            $lockedUser->save();

            $this->darkMatterTransactionService->recordTransaction(
                $lockedUser,
                -$offer['price'],
                $officer->transactionType()->value,
                $officer->label() . ' active until ' . $newExpiry->toDateTimeString(),
                $lockedUser->dark_matter,
            );

            return [
                'expires_at' => $newExpiry,
                'balance_after' => $lockedUser->dark_matter,
            ];
        }, 5);

        $user->refresh();

        return $result;
    }

    private function resolvePrice(OfficerType $officer): int
    {
        $default = max(1, (int) $this->settingsService->get('premium_officer_price', self::DEFAULT_PRICE));

        return max(1, (int) $this->settingsService->get('premium_' . $officer->value . '_price', $default));
    }

    private function resolveDurationSeconds(OfficerType $officer): int
    {
        $default = max(1, (int) $this->settingsService->get('premium_officer_duration_seconds', self::DEFAULT_DURATION_SECONDS));

        return max(1, (int) $this->settingsService->get('premium_' . $officer->value . '_duration_seconds', $default));
    }
}
