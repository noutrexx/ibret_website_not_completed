<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $key = 'ai_news_verify_ssl';
        $exists = DB::table('settings')->where('key', $key)->exists();

        if (!$exists) {
            DB::table('settings')->insert([
                'key' => $key,
                'value' => '0',
                'group' => 'general',
                'type' => 'bool',
                'is_public' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'ai_news_verify_ssl')->delete();
    }
};
