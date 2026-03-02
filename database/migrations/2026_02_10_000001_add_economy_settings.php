<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $rows = [
            ['key' => 'economy_category_slug', 'value' => 'ekonomi', 'group' => 'economy', 'type' => 'text', 'is_public' => true, 'autoload' => true],
            ['key' => 'economy_data_provider', 'value' => 'truncgil', 'group' => 'economy', 'type' => 'text', 'is_public' => false, 'autoload' => true],
            ['key' => 'economy_api_url', 'value' => 'https://finans.truncgil.com/v4/today.json', 'group' => 'economy', 'type' => 'text', 'is_public' => false, 'autoload' => true],
            ['key' => 'economy_currency_symbols', 'value' => 'USD,EUR,GBP,CHF,RUB,SAR', 'group' => 'economy', 'type' => 'text', 'is_public' => false, 'autoload' => true],
            ['key' => 'economy_gold_symbols', 'value' => 'GRA,CEYREKALTIN,YARIMALTIN,TAMALTIN,CUMHURIYETALTINI,GUMUS', 'group' => 'economy', 'type' => 'text', 'is_public' => false, 'autoload' => true],
            ['key' => 'economy_crypto_symbols', 'value' => 'BTC,ETH,USDT,BNB,XRP,SOL,DOGE,ADA', 'group' => 'economy', 'type' => 'text', 'is_public' => false, 'autoload' => true],
            ['key' => 'economy_market_symbols', 'value' => 'XU100,BRENT,ONS', 'group' => 'economy', 'type' => 'text', 'is_public' => false, 'autoload' => true],
            ['key' => 'cache_ttl_economy', 'value' => '60', 'group' => 'cache', 'type' => 'text', 'is_public' => false, 'autoload' => true],
        ];

        foreach ($rows as $row) {
            if (!DB::table('settings')->where('key', $row['key'])->exists()) {
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
            'economy_category_slug',
            'economy_data_provider',
            'economy_api_url',
            'economy_currency_symbols',
            'economy_gold_symbols',
            'economy_crypto_symbols',
            'economy_market_symbols',
            'cache_ttl_economy',
        ])->delete();
    }
};

