<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            ['key' => 'ai_news_enabled', 'value' => '0', 'group' => 'general', 'type' => 'bool', 'is_public' => 0],
            ['key' => 'ai_news_api_key', 'value' => '', 'group' => 'general', 'type' => 'text', 'is_public' => 0],
            ['key' => 'ai_news_base_url', 'value' => 'https://api.openai.com/v1', 'group' => 'general', 'type' => 'text', 'is_public' => 0],
            ['key' => 'ai_news_model', 'value' => 'gpt-4o-mini', 'group' => 'general', 'type' => 'text', 'is_public' => 0],
            ['key' => 'ai_news_prompt_style', 'value' => 'Tarafsız, bilgi odaklı, Türkçe haber dili.', 'group' => 'general', 'type' => 'textarea', 'is_public' => 0],
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
            'ai_news_enabled',
            'ai_news_api_key',
            'ai_news_base_url',
            'ai_news_model',
            'ai_news_prompt_style',
        ])->delete();
    }
};
