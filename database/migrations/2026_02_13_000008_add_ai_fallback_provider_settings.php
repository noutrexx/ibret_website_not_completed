<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            ['key' => 'ai_news_fallback_chain', 'value' => 'gemini,grok,groq', 'group' => 'general', 'type' => 'text', 'is_public' => 0],
            ['key' => 'ai_news_grok_api_key', 'value' => '', 'group' => 'general', 'type' => 'text', 'is_public' => 0],
            ['key' => 'ai_news_grok_base_url', 'value' => 'https://api.x.ai/v1', 'group' => 'general', 'type' => 'text', 'is_public' => 0],
            ['key' => 'ai_news_grok_model', 'value' => 'grok-2-latest', 'group' => 'general', 'type' => 'text', 'is_public' => 0],
            ['key' => 'ai_news_groq_api_key', 'value' => '', 'group' => 'general', 'type' => 'text', 'is_public' => 0],
            ['key' => 'ai_news_groq_base_url', 'value' => 'https://api.groq.com/openai/v1', 'group' => 'general', 'type' => 'text', 'is_public' => 0],
            ['key' => 'ai_news_groq_model', 'value' => 'llama-3.3-70b-versatile', 'group' => 'general', 'type' => 'text', 'is_public' => 0],
        ];

        foreach ($rows as $row) {
            $exists = DB::table('settings')->where('key', $row['key'])->exists();
            if (!$exists) {
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
            'ai_news_fallback_chain',
            'ai_news_grok_api_key',
            'ai_news_grok_base_url',
            'ai_news_grok_model',
            'ai_news_groq_api_key',
            'ai_news_groq_base_url',
            'ai_news_groq_model',
        ])->delete();
    }
};
