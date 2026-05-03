<?php

use Database\Seeders\AuctionLotTemplatesSeeder;
use Database\Seeders\AuctioneerSettingsSeeder;
use Database\Seeders\ShopCatalogSeeder;
use Database\Seeders\ShopExtendedDescriptionsSeeder;
use Database\Seeders\ShopRulesDescriptionsSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if ($this->hasSettingsTable()) {
            $this->seed(AuctioneerSettingsSeeder::class);
        }

        if ($this->hasAuctionTemplatesTable() && $this->isTableEmpty('auction_lot_templates')) {
            $this->seed(AuctionLotTemplatesSeeder::class);
        }

        if ($this->hasShopTables()) {
            $catalogMissing = $this->isTableEmpty('shop_categories') || $this->isTableEmpty('shop_items');
            if ($catalogMissing) {
                // Catalog seeder rebuilds categories + items + pivot in one pass.
                $this->seed(ShopCatalogSeeder::class);
            }

            // Descriptions are idempotent updates keyed by ref/name.
            $this->seed(ShopExtendedDescriptionsSeeder::class);
            $this->seed(ShopRulesDescriptionsSeeder::class);
        }
    }

    public function down(): void
    {
        // No-op: bootstrap-only migration.
    }

    private function seed(string $class): void
    {
        Artisan::call('db:seed', [
            '--class' => $class,
            '--force' => true,
        ]);
    }

    private function hasShopTables(): bool
    {
        return Schema::hasTable('shop_categories')
            && Schema::hasTable('shop_items')
            && Schema::hasTable('shop_item_category');
    }

    private function hasAuctionTemplatesTable(): bool
    {
        return Schema::hasTable('auction_lot_templates');
    }

    private function hasSettingsTable(): bool
    {
        return Schema::hasTable('settings');
    }

    private function isTableEmpty(string $table): bool
    {
        return DB::table($table)->count() === 0;
    }
};
