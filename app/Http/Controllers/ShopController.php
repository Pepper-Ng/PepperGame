<?php

namespace OGame\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends OGameController
{
    private const CATEGORY_SPECIAL_OFFERS = 'c18170d3125b9941ef3a86bd28dded7bf2066a6a';
    private const CATEGORY_ALL = 'd8d49c315fa620d9c7f1f19963970dea59a0e3be';
    private const CATEGORY_RESOURCES = 'e71139e15ee5b6f472e2c68a97aa4bae9c80e9da';
    private const CATEGORY_BUDDY_ITEMS = 'cccaafe693a53e8d1e791f06327974539da5978f';
    private const CATEGORY_CONSTRUCTION = 'dc9ec90e5a2163cc063b8bb3e9fe392782f565c8';

    /**
     * Shows the shop index page.
     */
    public function index(): View
    {
        $this->setBodyId('shop');

        return view('ingame.shop.index', [
            'shopItems' => $this->getShopItems(),
        ]);
    }

    /**
     * Returns the detail panel for a shop item.
     */
    public function detail(Request $request): View
    {
        $itemRef = (string) $request->query('type', '');
        $item = $this->findShopItem($itemRef);

        abort_unless($item !== null, 404);

        return view('ingame.shop.detail', [
            'item' => $item,
        ]);
    }

    /**
     * Returns the static list of shop booster items.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getShopItems(): array
    {
        return [
            // Gold boosters (6h / 7 000 DM)
            $this->buildShopItem(['ref' => '929d5e15709cc51a4500de4499e19763c879f7f7', 'name_key' => 'kraken',  'tier_key' => 'gold',   'rarity' => 'rare',     'duration' => '6h',  'price' => 7000, 'price_label' => '7K',   'image_hash' => '40a1644e104985a3e72da28b76069197128f9fb5'], [self::CATEGORY_SPECIAL_OFFERS, self::CATEGORY_ALL, self::CATEGORY_CONSTRUCTION]),
            $this->buildShopItem(['ref' => '0968999df2fe956aa4a07aea74921f860af7d97f', 'name_key' => 'detroid', 'tier_key' => 'gold',   'rarity' => 'rare',     'duration' => '6h',  'price' => 7000, 'price_label' => '7K',   'image_hash' => '55d4b1750985e4843023d7d0acd2b9bafb15f0b7'], [self::CATEGORY_SPECIAL_OFFERS, self::CATEGORY_ALL, self::CATEGORY_CONSTRUCTION]),
            $this->buildShopItem(['ref' => '8a4f9e8309e1078f7f5ced47d558d30ae15b4a1b', 'name_key' => 'newtron', 'tier_key' => 'gold',   'rarity' => 'rare',     'duration' => '6h',  'price' => 7000, 'price_label' => '7K',   'image_hash' => 'd949732b01a7f7f6d92e814f2de99479a324e1e3'], [self::CATEGORY_SPECIAL_OFFERS, self::CATEGORY_ALL, self::CATEGORY_CONSTRUCTION]),
            // Silver boosters (2h / 2 500 DM)
            $this->buildShopItem(['ref' => '4a58d4978bbe24e3efb3b0248e21b3b4b1bfbd8a', 'name_key' => 'kraken',  'tier_key' => 'silver', 'rarity' => 'uncommon', 'duration' => '2h',  'price' => 2500, 'price_label' => '2.5K', 'image_hash' => '1ee55efe00bb03743ca031a9eaa1374bb936d863'], [self::CATEGORY_ALL, self::CATEGORY_CONSTRUCTION]),
            $this->buildShopItem(['ref' => '27cbcd52f16693023cb966e5026d8a1efbbfc0f9', 'name_key' => 'detroid', 'tier_key' => 'silver', 'rarity' => 'uncommon', 'duration' => '2h',  'price' => 2500, 'price_label' => '2.5K', 'image_hash' => 'd0b8fb3d307b815b3182f3872e8eab654fe677df'], [self::CATEGORY_ALL, self::CATEGORY_CONSTRUCTION]),
            $this->buildShopItem(['ref' => 'd26f4dab76fdc5296e3ebec11a1e1d2558c713ea', 'name_key' => 'newtron', 'tier_key' => 'silver', 'rarity' => 'uncommon', 'duration' => '2h',  'price' => 2500, 'price_label' => '2.5K', 'image_hash' => 'a92734028d1bf2e75c5c25ae134b4d298a5ca36e'], [self::CATEGORY_ALL, self::CATEGORY_CONSTRUCTION]),
            // Bronze boosters (30m / 700 DM)
            $this->buildShopItem(['ref' => '40f6c78e11be01ad3389b7dccd6ab8efa9347f3c', 'name_key' => 'kraken',  'tier_key' => 'bronze', 'rarity' => 'common',   'duration' => '30m', 'price' => 700,  'price_label' => '700',  'image_hash' => '98629d11293c9f2703592ed0314d99f320f45845'], [self::CATEGORY_ALL, self::CATEGORY_CONSTRUCTION]),
            $this->buildShopItem(['ref' => 'd3d541ecc23e4daa0c698e44c32f04afd2037d84', 'name_key' => 'detroid', 'tier_key' => 'bronze', 'rarity' => 'common',   'duration' => '30m', 'price' => 700,  'price_label' => '700',  'image_hash' => '56724c3a1dcae8036bb172f0be833a6f9a28bc27'], [self::CATEGORY_ALL, self::CATEGORY_CONSTRUCTION]),
            $this->buildShopItem(['ref' => 'da4a2a1bb9afd410be07bc9736d87f1c8059e66d', 'name_key' => 'newtron', 'tier_key' => 'bronze', 'rarity' => 'common',   'duration' => '30m', 'price' => 700,  'price_label' => '700',  'image_hash' => '4bc4327a3fd508b5da84267e2cfd58d47f9e4dcb'], [self::CATEGORY_ALL, self::CATEGORY_CONSTRUCTION]),
        ];
    }

    /**
     * @param array<string, mixed> $item
     * @param array<int, string> $categoryRefs
     * @return array<string, mixed>
     */
    private function buildShopItem(array $item, array $categoryRefs): array
    {
        return array_merge($item, [
            'category_refs' => $categoryRefs,
            'title' => __('t_resources.' . $item['name_key'] . '.title') . ' ' . __('t_ingame.shop.tier_' . $item['tier_key']),
            'description' => __('t_resources.' . $item['name_key'] . '.description', [
                'duration' => '<b>' . $item['duration'] . '</b>',
            ]),
            'image_path' => '/img/icons/' . $item['image_hash'] . '-100x.png',
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findShopItem(string $itemRef): ?array
    {
        foreach ($this->getShopItems() as $item) {
            if ($item['ref'] === $itemRef) {
                return $item;
            }
        }

        return null;
    }
}
