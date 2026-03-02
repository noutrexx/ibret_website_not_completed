<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            ['key' => 'cache_ttl_home', 'value' => '300', 'group' => 'cache', 'type' => 'text', 'is_public' => false, 'autoload' => true],
            ['key' => 'cache_ttl_category', 'value' => '300', 'group' => 'cache', 'type' => 'text', 'is_public' => false, 'autoload' => true],
            ['key' => 'cache_ttl_search', 'value' => '60', 'group' => 'cache', 'type' => 'text', 'is_public' => false, 'autoload' => true],
        ];

        foreach ($rows as $row) {
            DB::table('settings')->updateOrInsert(['key' => $row['key']], $row);
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'cache_ttl_home',
            'cache_ttl_category',
            'cache_ttl_search',
        ])->delete();
    }
};
