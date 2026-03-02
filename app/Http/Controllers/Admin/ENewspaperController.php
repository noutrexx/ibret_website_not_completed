<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ENewspaper;
use App\Models\Post;
use App\Services\ENewspaper\ENewspaperGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ENewspaperController extends Controller
{
    private const HIDDEN_IN_EDITOR_GROUPS = ['SPOR', 'EKONOMI'];

    private const SLOT_MAP = [
        'manset' => 1,
        'gundem' => 8,
        'spor' => 4,
        'ekonomi' => 4,
        'yasam' => 5,
    ];

    private const SLOT_DEFINITIONS = [
        ['group' => 'MANSET', 'key' => 'manset_1', 'section' => 'manset', 'position' => 1, 'label' => 'Ana Manset'],
        ['group' => 'UST BANT', 'key' => 'gundem_1', 'section' => 'gundem', 'position' => 1, 'label' => 'Ust Bant 1'],
        ['group' => 'UST BANT', 'key' => 'gundem_2', 'section' => 'gundem', 'position' => 2, 'label' => 'Ust Bant 2'],
        ['group' => 'UST BANT', 'key' => 'gundem_3', 'section' => 'gundem', 'position' => 3, 'label' => 'Ust Bant 3'],
        ['group' => 'SAG KOLON 3LU', 'key' => 'gundem_4', 'section' => 'gundem', 'position' => 4, 'label' => 'Sag Kolon 1'],
        ['group' => 'SAG KOLON 3LU', 'key' => 'gundem_5', 'section' => 'gundem', 'position' => 5, 'label' => 'Sag Kolon 2'],
        ['group' => 'SAG KOLON 3LU', 'key' => 'gundem_6', 'section' => 'gundem', 'position' => 6, 'label' => 'Sag Kolon 3'],
        ['group' => 'IKINCI MANSET', 'key' => 'gundem_7', 'section' => 'gundem', 'position' => 7, 'label' => 'Ikinci Manset'],
        ['group' => 'IKINCI MANSET', 'key' => 'gundem_8', 'section' => 'gundem', 'position' => 8, 'label' => 'Yedek Ikinci Manset'],
        ['group' => 'SPOR', 'key' => 'spor_1', 'section' => 'spor', 'position' => 1, 'label' => 'Spor 1'],
        ['group' => 'SPOR', 'key' => 'spor_2', 'section' => 'spor', 'position' => 2, 'label' => 'Spor 2'],
        ['group' => 'SPOR', 'key' => 'spor_3', 'section' => 'spor', 'position' => 3, 'label' => 'Spor 3'],
        ['group' => 'SPOR', 'key' => 'spor_4', 'section' => 'spor', 'position' => 4, 'label' => 'Spor 4'],
        ['group' => 'EKONOMI', 'key' => 'ekonomi_1', 'section' => 'ekonomi', 'position' => 1, 'label' => 'Ekonomi 1'],
        ['group' => 'EKONOMI', 'key' => 'ekonomi_2', 'section' => 'ekonomi', 'position' => 2, 'label' => 'Ekonomi 2'],
        ['group' => 'EKONOMI', 'key' => 'ekonomi_3', 'section' => 'ekonomi', 'position' => 3, 'label' => 'Ekonomi 3'],
        ['group' => 'EKONOMI', 'key' => 'ekonomi_4', 'section' => 'ekonomi', 'position' => 4, 'label' => 'Ekonomi 4'],
        ['group' => 'SOL KOLON', 'key' => 'yasam_1', 'section' => 'yasam', 'position' => 1, 'label' => 'Sol Kolon 1'],
        ['group' => 'SOL KOLON', 'key' => 'yasam_2', 'section' => 'yasam', 'position' => 2, 'label' => 'Sol Kolon 2'],
        ['group' => 'SOL KOLON', 'key' => 'yasam_3', 'section' => 'yasam', 'position' => 3, 'label' => 'Sol Kolon 3'],
        ['group' => 'SOL KOLON', 'key' => 'yasam_4', 'section' => 'yasam', 'position' => 4, 'label' => 'Sol Kolon 4'],
        ['group' => 'SOL KOLON', 'key' => 'yasam_5', 'section' => 'yasam', 'position' => 5, 'label' => 'Sol Kolon 5'],
    ];

    public function index()
    {
        $issues = ENewspaper::query()
            ->withCount('items')
            ->with('creator:id,name')
            ->orderByDesc('issue_date')
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.e-newspapers.index', compact('issues'));
    }

    public function generate(Request $request, ENewspaperGeneratorService $generator)
    {
        $data = $request->validate([
            'issue_date' => ['required', 'date'],
        ]);

        $issue = $generator->generateForDate($data['issue_date'], auth()->id());

        return redirect()
            ->route('admin.e-newspapers.index')
            ->with('success', 'E-gazete taslagi olusturuldu/guncellendi: ' . $issue->title);
    }

    public function publish(int $id)
    {
        $issue = ENewspaper::findOrFail($id);
        $issue->status = 'published';
        $issue->published_at = now();
        $issue->save();

        return back()->with('success', 'E-gazete yayina alindi.');
    }

    public function unpublish(int $id)
    {
        $issue = ENewspaper::findOrFail($id);
        $issue->status = 'draft';
        $issue->published_at = null;
        $issue->save();

        return back()->with('success', 'E-gazete taslaga alindi.');
    }

    public function destroy(int $id)
    {
        ENewspaper::findOrFail($id)->delete();

        return back()->with('success', 'E-gazete silindi.');
    }

    public function edit(int $id)
    {
        $issue = ENewspaper::query()
            ->with(['items' => fn ($q) => $q->orderBy('section')->orderBy('position')])
            ->findOrFail($id);

        $latestPosts = Post::query()
            ->news()
            ->where('status', 'published')
            ->with('category:id,name')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->take(300)
            ->get(['id', 'category_id', 'title', 'published_at']);

        $slotValues = [];
        $slotImageControls = [];
        foreach ($this->editorSlotDefinitions() as $slot) {
            $match = $issue->items->first(fn ($item) => $item->section === $slot['section'] && (int) $item->position === (int) $slot['position']);
            $slotValues[$slot['key']] = (int) optional($match)->post_id;
            if ($slot['group'] === 'SOL KOLON') {
                $slotImageControls[$slot['key']] = optional($match)->show_image ?? true;
            }
        }

        $selectedIds = collect($slotValues)->filter()->map(fn ($v) => (int) $v)->unique()->values();
        $selectedPosts = collect();
        if ($selectedIds->isNotEmpty()) {
            $selectedPosts = Post::query()
                ->whereIn('id', $selectedIds)
                ->with('category:id,name')
                ->get(['id', 'category_id', 'title', 'published_at']);
        }

        $postOptions = $latestPosts
            ->concat($selectedPosts)
            ->unique('id')
            ->sortByDesc(fn ($p) => optional($p->published_at)->timestamp ?? 0)
            ->values();

        $slotGroups = collect($this->editorSlotDefinitions())->groupBy('group');

        return view('admin.e-newspapers.edit', [
            'issue' => $issue,
            'postOptions' => $postOptions,
            'slotMap' => self::SLOT_MAP,
            'slotValues' => $slotValues,
            'slotGroups' => $slotGroups,
            'slotImageControls' => $slotImageControls,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $issue = ENewspaper::findOrFail($id);

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:1000'],
            'issue_date' => ['required', 'date'],
        ];
        foreach ($this->editorSlotDefinitions() as $slot) {
            $rules['slots.' . $slot['key']] = ['nullable', 'integer', 'exists:posts,id'];
            if ($slot['group'] === 'SOL KOLON') {
                $rules['image_controls.' . $slot['key']] = ['nullable', 'boolean'];
            }
        }
        $rules['slots.manset_1'] = ['required', 'integer', 'exists:posts,id'];

        $data = $request->validate($rules);

        DB::transaction(function () use ($issue, $data) {
            $issue->title = $data['title'];
            $issue->slug = ENewspaper::uniqueSlug($data['title'], $issue->id);
            $issue->summary = $data['summary'] ?? null;
            $issue->issue_date = $data['issue_date'];
            $issue->save();

            $slotIds = collect(Arr::flatten($data['slots'] ?? []))
                ->filter()
                ->map(fn ($v) => (int) $v)
                ->unique()
                ->values();

            $posts = Post::query()
                ->whereIn('id', $slotIds)
                ->with('category:id,name')
                ->get()
                ->keyBy('id');

            $rows = [];
            foreach ($this->editorSlotDefinitions() as $slot) {
                $postId = (int) data_get($data, 'slots.' . $slot['key'], 0);
                if ($postId <= 0 || !$posts->has($postId)) {
                    continue;
                }

                $post = $posts->get($postId);
                $rows[] = [
                    'post_id' => $post->id,
                    'section' => $slot['section'],
                    'position' => (int) $slot['position'],
                    'title' => (string) $post->title,
                    'summary' => $post->summary ?: Str::limit(strip_tags((string) $post->content), 180),
                    'image' => $post->image,
                    'show_image' => $slot['group'] === 'SOL KOLON'
                        ? Arr::has($data, 'image_controls.' . $slot['key'])
                        : true,
                    'category_name' => $post->category?->name,
                    'post_url' => $post->frontend_url,
                    'post_published_at' => $post->published_at ?? $post->created_at,
                ];
            }

            $preservedRows = $issue->items()
                ->whereIn('section', ['spor', 'ekonomi'])
                ->get([
                    'post_id', 'section', 'position', 'title', 'summary', 'image',
                    'show_image', 'category_name', 'post_url', 'post_published_at',
                ])
                ->map(function ($item) {
                    return $item->only([
                        'post_id', 'section', 'position', 'title', 'summary', 'image',
                        'show_image', 'category_name', 'post_url', 'post_published_at',
                    ]);
                })
                ->values()
                ->all();

            $issue->items()->delete();
            $issue->items()->createMany(array_merge($rows, $preservedRows));
        });

        return redirect()->route('admin.e-newspapers.index')->with('success', 'E-gazete yerlesimi guncellendi.');
    }

    private function editorSlotDefinitions(): array
    {
        return array_values(array_filter(self::SLOT_DEFINITIONS, function (array $slot) {
            return !in_array($slot['group'], self::HIDDEN_IN_EDITOR_GROUPS, true);
        }));
    }
}
