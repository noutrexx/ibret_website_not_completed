<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            // SEO
            ['key' => 'seo_meta_title', 'value' => '{title}', 'group' => 'seo', 'type' => 'text', 'is_public' => true, 'autoload' => true],
            ['key' => 'seo_meta_description', 'value' => '', 'group' => 'seo', 'type' => 'textarea', 'is_public' => true, 'autoload' => true],
            ['key' => 'seo_meta_keywords', 'value' => '', 'group' => 'seo', 'type' => 'textarea', 'is_public' => true, 'autoload' => true],
            ['key' => 'seo_meta_author', 'value' => '', 'group' => 'seo', 'type' => 'text', 'is_public' => true, 'autoload' => true],
            ['key' => 'seo_meta_robots', 'value' => '', 'group' => 'seo', 'type' => 'text', 'is_public' => true, 'autoload' => true],
            ['key' => 'seo_og_type', 'value' => '', 'group' => 'seo', 'type' => 'text', 'is_public' => true, 'autoload' => true],
            ['key' => 'seo_og_site_name', 'value' => '', 'group' => 'seo', 'type' => 'text', 'is_public' => true, 'autoload' => true],
            ['key' => 'seo_og_image', 'value' => '', 'group' => 'seo', 'type' => 'image', 'is_public' => true, 'autoload' => true],
            ['key' => 'seo_og_image_width', 'value' => '', 'group' => 'seo', 'type' => 'text', 'is_public' => true, 'autoload' => true],
            ['key' => 'seo_og_image_height', 'value' => '', 'group' => 'seo', 'type' => 'text', 'is_public' => true, 'autoload' => true],
            ['key' => 'seo_og_locale', 'value' => 'tr_TR', 'group' => 'seo', 'type' => 'text', 'is_public' => true, 'autoload' => true],
            ['key' => 'seo_twitter_card', 'value' => 'summary_large_image', 'group' => 'seo', 'type' => 'text', 'is_public' => true, 'autoload' => true],
            ['key' => 'seo_twitter_site', 'value' => '', 'group' => 'seo', 'type' => 'text', 'is_public' => true, 'autoload' => true],
            ['key' => 'seo_twitter_creator', 'value' => '', 'group' => 'seo', 'type' => 'text', 'is_public' => true, 'autoload' => true],
            ['key' => 'seo_canonical_root', 'value' => '', 'group' => 'seo', 'type' => 'text', 'is_public' => true, 'autoload' => true],
            ['key' => 'seo_strip_query_params', 'value' => '0', 'group' => 'seo', 'type' => 'bool', 'is_public' => true, 'autoload' => true],
            ['key' => 'seo_jsonld_enabled', 'value' => '1', 'group' => 'seo', 'type' => 'bool', 'is_public' => true, 'autoload' => true],
            ['key' => 'seo_publisher_name', 'value' => '', 'group' => 'seo', 'type' => 'text', 'is_public' => true, 'autoload' => true],
            ['key' => 'seo_publisher_logo', 'value' => '', 'group' => 'seo', 'type' => 'image', 'is_public' => true, 'autoload' => true],
            ['key' => 'seo_article_author_enabled', 'value' => '1', 'group' => 'seo', 'type' => 'bool', 'is_public' => true, 'autoload' => true],
            ['key' => 'seo_article_section_default', 'value' => '', 'group' => 'seo', 'type' => 'text', 'is_public' => true, 'autoload' => true],
            ['key' => 'seo_news_keywords_enabled', 'value' => '0', 'group' => 'seo', 'type' => 'bool', 'is_public' => true, 'autoload' => true],
            ['key' => 'seo_news_default_keywords', 'value' => '', 'group' => 'seo', 'type' => 'textarea', 'is_public' => true, 'autoload' => true],
            ['key' => 'seo_sitemap_auto_ping', 'value' => '0', 'group' => 'seo', 'type' => 'bool', 'is_public' => false, 'autoload' => true],
            ['key' => 'seo_sitemap_changefreq_default', 'value' => 'daily', 'group' => 'seo', 'type' => 'text', 'is_public' => true, 'autoload' => true],
            ['key' => 'seo_sitemap_priority_default', 'value' => '0.8', 'group' => 'seo', 'type' => 'text', 'is_public' => true, 'autoload' => true],

            // Robots (var olanlarla uyum için)
            ['key' => 'robots_index', 'value' => 'index', 'group' => 'seo', 'type' => 'text', 'is_public' => true, 'autoload' => true],
            ['key' => 'robots_follow', 'value' => 'follow', 'group' => 'seo', 'type' => 'text', 'is_public' => true, 'autoload' => true],
            ['key' => 'robots_txt', 'value' => "User-agent: *\nAllow: /\nSitemap: ", 'group' => 'seo', 'type' => 'textarea', 'is_public' => true, 'autoload' => true],

            // Genel (var olanlarla uyum için)
            ['key' => 'site_title', 'value' => '', 'group' => 'general', 'type' => 'text', 'is_public' => true, 'autoload' => true],
        ];

        foreach ($rows as $row) {
            DB::table('settings')->updateOrInsert(['key' => $row['key']], $row);
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'seo_meta_title',
            'seo_meta_description',
            'seo_meta_keywords',
            'seo_meta_author',
            'seo_meta_robots',
            'seo_og_type',
            'seo_og_site_name',
            'seo_og_image',
            'seo_og_image_width',
            'seo_og_image_height',
            'seo_og_locale',
            'seo_twitter_card',
            'seo_twitter_site',
            'seo_twitter_creator',
            'seo_canonical_root',
            'seo_strip_query_params',
            'seo_jsonld_enabled',
            'seo_publisher_name',
            'seo_publisher_logo',
            'seo_article_author_enabled',
            'seo_article_section_default',
            'seo_news_keywords_enabled',
            'seo_news_default_keywords',
            'seo_sitemap_auto_ping',
            'seo_sitemap_changefreq_default',
            'seo_sitemap_priority_default',
            'robots_index',
            'robots_follow',
            'robots_txt',
            'site_title',
        ])->delete();
    }
};