<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $providerKey = 'ai_news_provider';
        if (!DB::table('settings')->where('key', $providerKey)->exists()) {
            DB::table('settings')->insert([
                'key' => $providerKey,
                'value' => 'gemini',
                'group' => 'general',
                'type' => 'text',
                'is_public' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // If old defaults are still in place, switch defaults to Gemini 2.5 Flash.
        $baseUrl = (string) DB::table('settings')->where('key', 'ai_news_base_url')->value('value');
        if ($baseUrl === '' || $baseUrl === 'https://api.openai.com/v1') {
            DB::table('settings')->updateOrInsert(
                ['key' => 'ai_news_base_url'],
                [
                    'value' => 'https://generativelanguage.googleapis.com/v1beta',
                    'group' => 'general',
                    'type' => 'text',
                    'is_public' => 0,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $model = (string) DB::table('settings')->where('key', 'ai_news_model')->value('value');
        if ($model === '' || $model === 'gpt-4o-mini') {
            DB::table('settings')->updateOrInsert(
                ['key' => 'ai_news_model'],
                [
                    'value' => 'gemini-2.5-flash',
                    'group' => 'general',
                    'type' => 'text',
                    'is_public' => 0,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'ai_news_provider')->delete();
    }
};
