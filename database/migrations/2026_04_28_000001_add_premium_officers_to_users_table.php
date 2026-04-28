<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('commander_until')->nullable()->after('character_class_changed_at');
            $table->timestamp('admiral_until')->nullable()->after('commander_until');
            $table->timestamp('engineer_until')->nullable()->after('admiral_until');
            $table->timestamp('geologist_until')->nullable()->after('engineer_until');
            $table->timestamp('technocrat_until')->nullable()->after('geologist_until');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'commander_until',
                'admiral_until',
                'engineer_until',
                'geologist_until',
                'technocrat_until',
            ]);
        });
    }
};
