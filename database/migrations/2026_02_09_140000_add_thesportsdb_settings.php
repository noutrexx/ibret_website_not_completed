<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            ['key' => 'sports_thesportsdb_base_url', 'value' => 'https://www.thesportsdb.com/api/v1/json', 'group' => 'sports', 'type' => 'text', 'is_public' => false, 'autoload' => true],
            ['key' => 'sports_thesportsdb_key', 'value' => '123', 'group' => 'sports', 'type' => 'text', 'is_public' => false, 'autoload' => true],
            ['key' => 'sports_thesportsdb_league_id', 'value' => '4339', 'group' => 'sports', 'type' => 'text', 'is_public' => false, 'autoload' => true],
            ['key' => 'sports_thesportsdb_season', 'value' => (date('Y') - 1) . '-' . date('Y'), 'group' => 'sports', 'type' => 'text', 'is_public' => false, 'autoload' => true],
        ];

        foreach ($rows as $row) {
            DB::table('settings')->updateOrInsert(['key' => $row['key']], $row);
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'sports_thesportsdb_base_url',
            'sports_thesportsdb_key',
            'sports_thesportsdb_league_id',
            'sports_thesportsdb_season',
        ])->delete();
    }
};
