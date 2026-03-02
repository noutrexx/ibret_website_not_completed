<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\RssSource;
use Illuminate\Database\Seeder;

class RssSourcesSeeder extends Seeder
{
    public function run(): void
    {
        $categoryIds = Category::query()->pluck('id', 'slug')->toArray();

        $sportId = $categoryIds[(string) setting('sports_category_slug', 'spor')] ?? ($categoryIds['spor'] ?? null);
        $economyId = $categoryIds[(string) setting('economy_category_slug', 'ekonomi')] ?? ($categoryIds['ekonomi'] ?? null);
        $techId = $categoryIds['teknoloji'] ?? null;
        $healthId = $categoryIds['saglik'] ?? null;
        $educationId = $categoryIds['egitim'] ?? null;
        $lifeId = $categoryIds['yasam'] ?? null;
        $automobileId = $categoryIds['otomobil'] ?? null;
        $defaultId = $categoryIds['turkiye'] ?? $categoryIds['gundem'] ?? null;

        $sources = [
            ['name' => 'NTV Egitim', 'feed_url' => 'https://www.ntv.com.tr/egitim.rss', 'default_category_id' => $educationId ?: $defaultId],
            ['name' => 'NTV Otomobil', 'feed_url' => 'https://www.ntv.com.tr/otomobil.rss', 'default_category_id' => $automobileId ?: $defaultId],
            ['name' => 'NTV Saglik', 'feed_url' => 'https://www.ntv.com.tr/saglik.rss', 'default_category_id' => $healthId ?: $defaultId],
            ['name' => 'NTV Yasam', 'feed_url' => 'https://www.ntv.com.tr/yasam.rss', 'default_category_id' => $lifeId ?: $defaultId],
            ['name' => 'NTV Teknoloji', 'feed_url' => 'https://www.ntv.com.tr/teknoloji.rss', 'default_category_id' => $techId ?: $defaultId],
            ['name' => 'NTV Ekonomi', 'feed_url' => 'https://www.ntv.com.tr/ekonomi.rss', 'default_category_id' => $economyId ?: $defaultId],
            ['name' => 'NTV Dunya', 'feed_url' => 'https://www.ntv.com.tr/dunya.rss', 'default_category_id' => $defaultId],
            ['name' => 'NTV Turkiye', 'feed_url' => 'https://www.ntv.com.tr/turkiye.rss', 'default_category_id' => $defaultId],
            ['name' => 'NTV Para', 'feed_url' => 'https://www.ntv.com.tr/ntvpara.rss', 'default_category_id' => $economyId ?: $defaultId],
            ['name' => 'NTV Spor Skor', 'feed_url' => 'https://www.ntv.com.tr/sporskor.rss', 'default_category_id' => $sportId ?: $defaultId],
            ['name' => 'Milliyet Son Dakika', 'feed_url' => 'https://www.milliyet.com.tr/rss/rssnew/sondakikarss.xml', 'default_category_id' => $defaultId],
        ];

        $allowedUrls = collect($sources)->pluck('feed_url')->values()->all();

        RssSource::query()->whereNotIn('feed_url', $allowedUrls)->delete();

        foreach ($sources as $row) {
            RssSource::updateOrCreate(
                ['feed_url' => $row['feed_url']],
                [
                    'name' => $row['name'],
                    'source_domain' => parse_url($row['feed_url'], PHP_URL_HOST) ?: null,
                    'default_category_id' => $row['default_category_id'],
                    'is_active' => true,
                    'fetch_interval_minutes' => 15,
                ]
            );
        }
    }
}
