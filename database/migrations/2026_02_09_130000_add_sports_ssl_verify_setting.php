<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->updateOrInsert(
            ['key' => 'sports_api_verify_ssl'],
            [
                'key' => 'sports_api_verify_ssl',
                'value' => '0',
                'group' => 'sports',
                'type' => 'text',
                'is_public' => false,
                'autoload' => true,
            ]
        );
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'sports_api_verify_ssl')->delete();
    }
};
