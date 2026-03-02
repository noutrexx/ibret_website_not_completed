<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->whereIn('key', [
            'sports_api_football_base_url',
            'sports_api_football_host',
            'sports_api_football_key',
            'sports_api_football_league_id',
            'sports_api_football_season',
            'sports_api_football_daily_budget',
            'sports_api_big_three_team_ids',
            'sports_football_data_base_url',
            'sports_football_data_key',
            'sports_football_data_competitions',
            'sports_thesportsdb_base_url',
            'sports_thesportsdb_key',
            'sports_thesportsdb_league_id',
            'sports_thesportsdb_season',
            'sports_api_verify_ssl',
            'sports_soccerway_standings_url',
            'sports_soccerway_fixtures_url',
            'sports_soccerway_leagues',
            'sports_data_provider',
            'cache_ttl_sports_live',
            'cache_ttl_sports_europe',
        ])->delete();

        $rows = [
            ['key' => 'sports_category_slug', 'value' => 'spor', 'group' => 'sports', 'type' => 'text', 'is_public' => true, 'autoload' => true],
            ['key' => 'sports_custom_api_base_url', 'value' => 'http://45.87.173.75:8000/v1/football', 'group' => 'sports', 'type' => 'text', 'is_public' => false, 'autoload' => true],
            ['key' => 'sports_custom_default_league', 'value' => 'super_lig', 'group' => 'sports', 'type' => 'text', 'is_public' => false, 'autoload' => true],
            ['key' => 'sports_custom_enabled_leagues', 'value' => 'super_lig,tff_1_lig,premier_league,laliga,serie_a,bundesliga', 'group' => 'sports', 'type' => 'textarea', 'is_public' => false, 'autoload' => true],
            ['key' => 'sports_custom_api_timeout', 'value' => '15', 'group' => 'sports', 'type' => 'text', 'is_public' => false, 'autoload' => true],
            ['key' => 'cache_ttl_sports_standings', 'value' => '360', 'group' => 'cache', 'type' => 'text', 'is_public' => false, 'autoload' => true],
            ['key' => 'cache_ttl_sports_fixtures', 'value' => '120', 'group' => 'cache', 'type' => 'text', 'is_public' => false, 'autoload' => true],
        ];

        foreach ($rows as $row) {
            DB::table('settings')->updateOrInsert(['key' => $row['key']], $row);
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'sports_custom_api_base_url',
            'sports_custom_default_league',
            'sports_custom_enabled_leagues',
            'sports_custom_api_timeout',
        ])->delete();
    }
};
