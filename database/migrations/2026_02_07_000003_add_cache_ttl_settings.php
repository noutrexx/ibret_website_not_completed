<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            ['key' => 'cache_ttl_post_show', 'value' => '600', 'group' => 'cache', 'type' => 'text', 'is_public' => false, 'autoload' => true],
            ['key' => 'cache_ttl_sitemap', 'value' => '600', 'group' => 'cache', 'type' => 'text', 'is_public' => false, 'autoload' => true],
        ];

        foreach ($rows as $row) {
            DB::table('settings')->updateOrInsert(['key' => $row['key']], $row);
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'cache_ttl_post_show',
            'cache_ttl_sitemap',
        ])->delete();
    }
};