<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $defaults = [
            [
                'key' => 'sports_soccerway_standings_url',
                'value' => 'https://tr.soccerway.com/turkiye/super-lig/',
                'group' => 'sports',
                'type' => 'text',
                'is_public' => false,
                'autoload' => true,
            ],
            [
                'key' => 'sports_soccerway_fixtures_url',
                'value' => 'https://tr.soccerway.com/turkiye/super-lig/',
                'group' => 'sports',
                'type' => 'text',
                'is_public' => false,
                'autoload' => true,
            ],
            [
                'key' => 'sports_data_provider',
                'value' => 'soccerway',
                'group' => 'sports',
                'type' => 'text',
                'is_public' => false,
                'autoload' => true,
            ],
        ];

        foreach ($defaults as $row) {
            $exists = DB::table('settings')->where('key', $row['key'])->exists();
            if (!$exists) {
                DB::table('settings')->insert(array_merge($row, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'sports_soccerway_standings_url',
            'sports_soccerway_fixtures_url',
            'sports_data_provider',
        ])->delete();
    }
};
