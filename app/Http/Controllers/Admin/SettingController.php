<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::orderBy('group')->orderBy('key')->get();
        $sitemapUrl = url('/sitemap.xml');
        $sitemapCount = Post::where('status', 'published')->count();

        return view('admin.settings.index', compact('settings', 'sitemapUrl', 'sitemapCount'));
    }

    public function update(Request $request)
    {
        $incoming = $request->input('settings', []);
        $files = $request->file('settings', []);

        $boolKeys = Setting::where('type', 'bool')->pluck('key')->toArray();
        foreach ($boolKeys as $key) {
            if (!array_key_exists($key, $incoming)) {
                $incoming[$key] = '0';
            }
        }

        $imageKeys = Setting::where('type', 'image')->pluck('key')->toArray();
        foreach ($imageKeys as $imgKey) {
            if (!array_key_exists($imgKey, $incoming)) {
                $incoming[$imgKey] = null;
            }
        }

        foreach ($incoming as $key => $value) {
            if (in_array($key, $imageKeys, true)) {
                if (isset($files[$key]) && $files[$key]->isValid()) {
                    $value = $files[$key]->store('settings', 'public');
                } else {
                    $value = Setting::where('key', $key)->value('value');
                }
            }

            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => is_array($value) ? json_encode($value) : (string) $value]
            );
            cache()->forget('setting_' . $key);
        }

        return back()->with('success', 'Ayarlar güncellendi.');
    }

    public function robots()
    {
        $content = setting('robots_txt', "User-agent: *\nAllow: /\nSitemap: " . url('/sitemap.xml'));

        return response($content, 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    public function pingSitemap()
    {
        $sitemapUrl = url('/sitemap.xml');
        $results = [];

        try {
            $google = Http::timeout(5)->get('https://www.google.com/ping', ['sitemap' => $sitemapUrl]);
            $results['google'] = $google->successful() ? 'OK' : 'Hata: ' . $google->status();
        } catch (\Throwable $e) {
            $results['google'] = 'Hata: ' . $e->getMessage();
        }

        try {
            $bing = Http::timeout(5)->get('https://www.bing.com/ping', ['siteMap' => $sitemapUrl]);
            $results['bing'] = $bing->successful() ? 'OK' : 'Hata: ' . $bing->status();
        } catch (\Throwable $e) {
            $results['bing'] = 'Hata: ' . $e->getMessage();
        }

        return back()->with('success', 'Sitemap ping sonucu: Google=' . $results['google'] . ' | Bing=' . $results['bing']);
    }
}
