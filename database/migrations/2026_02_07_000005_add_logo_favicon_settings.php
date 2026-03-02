<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            ['key' => 'site_logo', 'value' => '', 'group' => 'general', 'type' => 'image', 'is_public' => true, 'autoload' => true],
            ['key' => 'site_favicon', 'value' => '', 'group' => 'general', 'type' => 'image', 'is_public' => true, 'autoload' => true],
        ];

        foreach ($rows as $row) {
            DB::table('settings')->updateOrInsert(['key' => $row['key']], $row);
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', ['site_logo', 'site_favicon'])->delete();
    }
};
