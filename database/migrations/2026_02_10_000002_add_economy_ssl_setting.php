<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!DB::table('settings')->where('key', 'economy_api_verify_ssl')->exists()) {
            DB::table('settings')->insert([
                'key' => 'economy_api_verify_ssl',
                'value' => '0',
                'group' => 'economy',
                'type' => 'bool',
                'is_public' => false,
                'autoload' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'economy_api_verify_ssl')->delete();
    }
};

