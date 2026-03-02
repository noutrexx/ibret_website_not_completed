<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->updateOrInsert(
            ['key' => 'sports_custom_api_key'],
            [
                'key' => 'sports_custom_api_key',
                'value' => 'dc8a6b6d-e53e-47d0-9424-3dd7dd9ee82c',
                'group' => 'sports',
                'type' => 'text',
                'is_public' => false,
                'autoload' => true,
            ]
        );
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'sports_custom_api_key')->delete();
    }
};
