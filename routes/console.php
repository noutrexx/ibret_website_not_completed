<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\Sports\CustomFootballApiService;
use App\Services\Rss\RssIngestService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('sports:soccerway-sync', function (CustomFootballApiService $service) {
    $results = $service->refreshAll();
    $hasError = false;

    foreach ($results as $leagueKey => $result) {
        if (!($result['ok'] ?? false)) {
            $hasError = true;
            $this->error("[$leagueKey] {$result['name']} sync basarisiz: " . ($result['error'] ?? 'bilinmeyen hata'));
            continue;
        }

        $standings = $service->standings($leagueKey);
        $fixtures = $service->fixtures($leagueKey);

        $this->info("[$leagueKey] {$result['name']} sync tamamlandi.");
        $this->line('Puan satiri: ' . count($standings));
        $this->line('Fikstur satiri: ' . count($fixtures));
    }

    return $hasError ? 1 : 0;
})->purpose('Fetch standings and fixtures from custom football API');

Schedule::command('sports:soccerway-sync')
    ->everyThirtyMinutes()
    ->withoutOverlapping()
    ->runInBackground();

Artisan::command('news:ingest-rss {--source_id=} {--limit=40}', function (RssIngestService $service) {
    $sourceId = $this->option('source_id') ? (int) $this->option('source_id') : null;
    $limit = max(1, (int) $this->option('limit'));
    $result = $service->ingest($sourceId, $limit);

    $this->info('Kaynak: ' . $result['sources']);
    $this->info('Yeni: ' . $result['inserted']);
    $this->info('Tekrar: ' . $result['duplicates']);

    foreach ($result['errors'] as $error) {
        $this->error($error);
    }

    return empty($result['errors']) ? 0 : 1;
})->purpose('RSS kaynaklarindan haber havuzuna icerik ceker');

Schedule::command('news:ingest-rss --limit=30')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->runInBackground();
