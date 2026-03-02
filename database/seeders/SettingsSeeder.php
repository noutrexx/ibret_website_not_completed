<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // GENEL
            ['key' => 'site_title', 'value' => 'İbret Haber', 'group' => 'general', 'type' => 'text', 'is_public' => true],
            ['key' => 'site_tagline', 'value' => 'Güncel haberler ve köşe yazıları', 'group' => 'general', 'type' => 'text', 'is_public' => true],
            ['key' => 'site_language', 'value' => 'tr', 'group' => 'general', 'type' => 'text', 'is_public' => true],
            ['key' => 'posts_per_page', 'value' => '12', 'group' => 'general', 'type' => 'text', 'is_public' => true],

            // SEO
            ['key' => 'seo_meta_description', 'value' => 'Türkiye ve dünyadan son dakika haberleri.', 'group' => 'seo', 'type' => 'textarea', 'is_public' => true],
            ['key' => 'seo_meta_keywords', 'value' => 'haber, son dakika, gündem, ekonomi', 'group' => 'seo', 'type' => 'text', 'is_public' => true],
            ['key' => 'seo_og_image', 'value' => null, 'group' => 'seo', 'type' => 'image', 'is_public' => true],

            // GÖRSEL
            ['key' => 'logo', 'value' => null, 'group' => 'assets', 'type' => 'image', 'is_public' => true],
            ['key' => 'mobile_logo', 'value' => null, 'group' => 'assets', 'type' => 'image', 'is_public' => true],
            ['key' => 'favicon', 'value' => null, 'group' => 'assets', 'type' => 'image', 'is_public' => true],
            ['key' => 'default_post_image', 'value' => null, 'group' => 'assets', 'type' => 'image', 'is_public' => true],

            // SOSYAL
            ['key' => 'facebook_url', 'value' => null, 'group' => 'social', 'type' => 'text', 'is_public' => true],
            ['key' => 'twitter_url', 'value' => null, 'group' => 'social', 'type' => 'text', 'is_public' => true],
            ['key' => 'instagram_url', 'value' => null, 'group' => 'social', 'type' => 'text', 'is_public' => true],
            ['key' => 'youtube_url', 'value' => null, 'group' => 'social', 'type' => 'text', 'is_public' => true],

            // ANALYTICS
            ['key' => 'google_analytics_id', 'value' => null, 'group' => 'analytics', 'type' => 'text', 'is_public' => false],
            ['key' => 'google_search_console', 'value' => null, 'group' => 'analytics', 'type' => 'text', 'is_public' => false],

            // SİTE DAVRANIŞI
            ['key' => 'maintenance_mode', 'value' => '0', 'group' => 'system', 'type' => 'bool', 'is_public' => false],
            ['key' => 'cache_enabled', 'value' => '1', 'group' => 'system', 'type' => 'bool', 'is_public' => false],

            ['key' => 'robots_index', 'value' => 'index', 'group' => 'seo', 'type' => 'text', 'is_public' => true],
            ['key' => 'robots_follow', 'value' => 'follow', 'group' => 'seo', 'type' => 'text', 'is_public' => true],
            ['key' => 'robots_txt', 'value' => "User-agent: *\nAllow: /\nSitemap: " . url('/sitemap.xml'), 'group' => 'seo', 'type' => 'textarea', 'is_public' => false],

            ['key' => 'seo_canonical_enabled', 'value' => '1', 'group' => 'seo', 'type' => 'bool', 'is_public' => true],
            ['key' => 'seo_jsonld_enabled', 'value' => '1', 'group' => 'seo', 'type' => 'bool', 'is_public' => true],
            ['key' => 'seo_twitter_card', 'value' => 'summary_large_image', 'group' => 'seo', 'type' => 'text', 'is_public' => true],


        ];

        foreach ($defaults as $row) {
            Setting::updateOrCreate(
                ['key' => $row['key']],
                [
                    'value' => $row['value'],
                    'group' => $row['group'] ?? 'general',
                    'type' => $row['type'] ?? 'text',
                    'is_public' => $row['is_public'] ?? false,
                    'autoload' => $row['autoload'] ?? true,
                ]
            );
        }
    }
}
