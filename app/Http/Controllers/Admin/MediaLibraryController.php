<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaLibraryController extends Controller
{
    public function index(Request $request)
    {
        $tab = (string) $request->query('tab', 'library');
        $q = trim((string) $request->query('q', ''));
        $favoritesOnly = $tab === 'favorites' || (string) $request->query('favorites') === '1';

        $items = MediaAsset::query()
            ->when($favoritesOnly, fn ($query) => $query->where('is_favorite', true))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('title', 'like', '%' . $q . '%')
                        ->orWhere('original_name', 'like', '%' . $q . '%')
                        ->orWhere('alt_text', 'like', '%' . $q . '%')
                        ->orWhere('path', 'like', '%' . $q . '%');
                });
            })
            ->orderByDesc('is_favorite')
            ->latest('id')
            ->paginate(24);

        return response()->json([
            'ok' => true,
            'items' => $items->getCollection()->map(fn (MediaAsset $item) => $this->toPayload($item))->values(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    public function upload(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|image|mimes:jpeg,jpg,png,webp,gif|max:5120',
        ]);

        $file = $validated['file'];
        $hash = hash_file('sha256', $file->getRealPath());

        $existing = MediaAsset::where('hash', $hash)->first();
        if ($existing) {
            return response()->json([
                'ok' => true,
                'asset' => $this->toPayload($existing),
                'duplicate' => true,
            ]);
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $name = now()->format('Ymd_His') . '_' . Str::random(8) . '.' . $ext;
        $path = $file->storeAs('media-library', $name, 'public');

        [$width, $height] = $this->detectImageDimensions($file->getRealPath());

        $asset = MediaAsset::create([
            'user_id' => auth()->id(),
            'disk' => 'public',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'width' => $width,
            'height' => $height,
            'hash' => $hash,
            'source_provider' => 'local',
        ]);

        return response()->json([
            'ok' => true,
            'asset' => $this->toPayload($asset),
        ]);
    }

    public function toggleFavorite(int $id)
    {
        $asset = MediaAsset::findOrFail($id);
        $asset->is_favorite = !$asset->is_favorite;
        $asset->save();

        return response()->json(['ok' => true, 'asset' => $this->toPayload($asset)]);
    }

    public function freeSearch(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['ok' => false, 'message' => 'Arama icin en az 2 karakter girin.'], 422);
        }

        $resp = Http::timeout(20)
            ->acceptJson()
            ->get('https://commons.wikimedia.org/w/api.php', [
                'action' => 'query',
                'format' => 'json',
                'generator' => 'search',
                'gsrsearch' => $q . ' filetype:bitmap',
                'gsrnamespace' => 6,
                'gsrlimit' => 20,
                'prop' => 'imageinfo',
                'iiprop' => 'url|size|mime|extmetadata',
                'iiurlwidth' => 640,
                'origin' => '*',
            ]);

        if (!$resp->successful()) {
            return response()->json(['ok' => false, 'message' => 'Ucretsiz resim aramasi basarisiz.'], 500);
        }

        $pages = collect(data_get($resp->json(), 'query.pages', []));
        $items = $pages->map(function ($page) {
            $info = collect($page['imageinfo'] ?? [])->first();
            if (!$info || empty($info['thumburl']) || empty($info['url'])) {
                return null;
            }

            $credit = trim(strip_tags((string) data_get($info, 'extmetadata.Artist.value', '')));
            $license = trim(strip_tags((string) data_get($info, 'extmetadata.LicenseShortName.value', '')));

            return [
                'id' => 'wm:' . (string) ($page['pageid'] ?? Str::random(8)),
                'title' => (string) ($page['title'] ?? 'Wikimedia image'),
                'thumb_url' => (string) $info['thumburl'],
                'source_url' => (string) $info['url'],
                'mime_type' => (string) ($info['mime'] ?? ''),
                'width' => (int) ($info['width'] ?? 0),
                'height' => (int) ($info['height'] ?? 0),
                'credit' => $credit,
                'license' => $license,
                'provider' => 'wikimedia',
            ];
        })->filter()->values();

        return response()->json(['ok' => true, 'items' => $items]);
    }

    public function importRemote(Request $request)
    {
        $validated = $request->validate([
            'source_url' => 'required|url|max:1500',
            'title' => 'nullable|string|max:255',
            'credit' => 'nullable|string|max:255',
            'provider' => 'nullable|string|max:60',
        ]);

        $url = (string) $validated['source_url'];

        $response = Http::timeout(30)->withOptions(['verify' => false])->get($url);
        if (!$response->successful()) {
            return response()->json(['ok' => false, 'message' => 'Resim indirilemedi.'], 422);
        }

        $body = $response->body();
        $mime = (string) ($response->header('Content-Type') ?? '');
        if (!Str::startsWith($mime, 'image/')) {
            return response()->json(['ok' => false, 'message' => 'Gelen dosya resim degil.'], 422);
        }

        $hash = hash('sha256', $body);
        $existing = MediaAsset::where('hash', $hash)->first();
        if ($existing) {
            return response()->json(['ok' => true, 'asset' => $this->toPayload($existing), 'duplicate' => true]);
        }

        $ext = $this->extensionFromMime($mime);
        $path = 'media-library/' . now()->format('Ymd_His') . '_' . Str::random(8) . '.' . $ext;
        Storage::disk('public')->put($path, $body);

        $tmpFile = tempnam(sys_get_temp_dir(), 'med');
        file_put_contents($tmpFile, $body);
        [$width, $height] = $this->detectImageDimensions($tmpFile);
        @unlink($tmpFile);

        $asset = MediaAsset::create([
            'user_id' => auth()->id(),
            'disk' => 'public',
            'path' => $path,
            'original_name' => basename(parse_url($url, PHP_URL_PATH) ?: $path),
            'title' => $validated['title'] ?: 'Remote image',
            'mime_type' => $mime,
            'size' => strlen($body),
            'width' => $width,
            'height' => $height,
            'hash' => $hash,
            'source_provider' => $validated['provider'] ?? 'remote',
            'source_url' => $url,
            'credit' => $validated['credit'] ?? null,
        ]);

        return response()->json(['ok' => true, 'asset' => $this->toPayload($asset)]);
    }

    private function toPayload(MediaAsset $item): array
    {
        return [
            'id' => $item->id,
            'path' => $item->path,
            'url' => $item->url,
            'title' => $item->title,
            'alt_text' => $item->alt_text,
            'original_name' => $item->original_name,
            'mime_type' => $item->mime_type,
            'size' => $item->size,
            'width' => $item->width,
            'height' => $item->height,
            'is_favorite' => (bool) $item->is_favorite,
            'source_provider' => $item->source_provider,
            'source_url' => $item->source_url,
            'credit' => $item->credit,
            'created_at' => optional($item->created_at)->toDateTimeString(),
        ];
    }

    private function detectImageDimensions(string $path): array
    {
        $size = @getimagesize($path);
        return [
            (int) ($size[0] ?? 0),
            (int) ($size[1] ?? 0),
        ];
    }

    private function extensionFromMime(string $mime): string
    {
        return match (Str::lower(trim(explode(';', $mime)[0]))) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            default => 'jpg',
        };
    }
}

