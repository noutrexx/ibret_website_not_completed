<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            [
                'key' => 'economy_custom_api_base_url',
                'value' => 'http://45.87.173.75:8000/v1/latest',
                'group' => 'economy',
                'type' => 'text',
                'is_public' => false,
                'autoload' => true,
            ],
            [
                'key' => 'economy_custom_api_key',
                'value' => 'dc8a6b6d-e53e-47d0-9424-3dd7dd9ee82c',
                'group' => 'economy',
                'type' => 'text',
                'is_public' => false,
                'autoload' => true,
            ],
        ];

        foreach ($rows as $row) {
            DB::table('settings')->updateOrInsert(['key' => $row['key']], $row);
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'economy_custom_api_base_url',
            'economy_custom_api_key',
        ])->delete();
    }
};
