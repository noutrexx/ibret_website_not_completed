<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            ['key' => 'sports_category_slug', 'value' => 'spor', 'group' => 'sports', 'type' => 'text', 'is_public' => true, 'autoload' => true],
            ['key' => 'sports_api_football_base_url', 'value' => 'https://v3.football.api-sports.io', 'group' => 'sports', 'type' => 'text', 'is_public' => false, 'autoload' => true],
            ['key' => 'sports_api_football_host', 'value' => 'api-football-v1.p.rapidapi.com', 'group' => 'sports', 'type' => 'text', 'is_public' => false, 'autoload' => true],
            ['key' => 'sports_api_football_key', 'value' => null, 'group' => 'sports', 'type' => 'text', 'is_public' => false, 'autoload' => true],
            ['key' => 'sports_api_football_league_id', 'value' => '203', 'group' => 'sports', 'type' => 'text', 'is_public' => false, 'autoload' => true],
            ['key' => 'sports_api_football_season', 'value' => (string) date('Y'), 'group' => 'sports', 'type' => 'text', 'is_public' => false, 'autoload' => true],
            ['key' => 'sports_api_football_daily_budget', 'value' => '95', 'group' => 'sports', 'type' => 'text', 'is_public' => false, 'autoload' => true],
            ['key' => 'sports_api_big_three_team_ids', 'value' => '', 'group' => 'sports', 'type' => 'text', 'is_public' => false, 'autoload' => true],
            ['key' => 'sports_football_data_base_url', 'value' => 'https://api.football-data.org/v4', 'group' => 'sports', 'type' => 'text', 'is_public' => false, 'autoload' => true],
            ['key' => 'sports_football_data_key', 'value' => null, 'group' => 'sports', 'type' => 'text', 'is_public' => false, 'autoload' => true],
            ['key' => 'sports_football_data_competitions', 'value' => 'PL,PD,BL1,SA,FL1', 'group' => 'sports', 'type' => 'text', 'is_public' => false, 'autoload' => true],
            ['key' => 'cache_ttl_sports_standings', 'value' => '360', 'group' => 'cache', 'type' => 'text', 'is_public' => false, 'autoload' => true],
            ['key' => 'cache_ttl_sports_fixtures', 'value' => '120', 'group' => 'cache', 'type' => 'text', 'is_public' => false, 'autoload' => true],
            ['key' => 'cache_ttl_sports_live', 'value' => '5', 'group' => 'cache', 'type' => 'text', 'is_public' => false, 'autoload' => true],
            ['key' => 'cache_ttl_sports_europe', 'value' => '360', 'group' => 'cache', 'type' => 'text', 'is_public' => false, 'autoload' => true],
        ];

        foreach ($rows as $row) {
            DB::table('settings')->updateOrInsert(['key' => $row['key']], $row);
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'sports_category_slug',
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
            'cache_ttl_sports_standings',
            'cache_ttl_sports_fixtures',
            'cache_ttl_sports_live',
            'cache_ttl_sports_europe',
        ])->delete();
    }
};
