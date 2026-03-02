<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $key = 'sports_soccerway_leagues';
        $exists = DB::table('settings')->where('key', $key)->exists();

        if (!$exists) {
            $value = json_encode([
                [
                    'key' => 'super-lig',
                    'name' => 'Super Lig',
                    'standings_url' => 'https://tr.soccerway.com/turkiye/super-lig/',
                    'fixtures_url' => 'https://tr.soccerway.com/turkiye/super-lig/',
                ],
                [
                    'key' => 'premier-league',
                    'name' => 'Premier League',
                    'standings_url' => 'https://tr.soccerway.com/ingiltere/premier-league/',
                    'fixtures_url' => 'https://tr.soccerway.com/ingiltere/premier-league/',
                ],
                [
                    'key' => 'laliga',
                    'name' => 'LaLiga',
                    'standings_url' => 'https://tr.soccerway.com/ispanya/primera-division/',
                    'fixtures_url' => 'https://tr.soccerway.com/ispanya/primera-division/',
                ],
                [
                    'key' => 'serie-a',
                    'name' => 'Serie A',
                    'standings_url' => 'https://tr.soccerway.com/italya/serie-a/',
                    'fixtures_url' => 'https://tr.soccerway.com/italya/serie-a/',
                ],
                [
                    'key' => 'bundesliga',
                    'name' => 'Bundesliga',
                    'standings_url' => 'https://tr.soccerway.com/almanya/bundesliga/',
                    'fixtures_url' => 'https://tr.soccerway.com/almanya/bundesliga/',
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

            DB::table('settings')->insert([
                'key' => $key,
                'value' => $value,
                'group' => 'sports',
                'type' => 'textarea',
                'is_public' => false,
                'autoload' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'sports_soccerway_leagues')->delete();
    }
};

