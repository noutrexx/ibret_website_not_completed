<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\ENewspaper;
use Illuminate\Http\Request;

class ENewspaperController extends Controller
{
    public function index()
    {
        $issues = ENewspaper::query()
            ->where('status', 'published')
            ->with(['items' => function ($q) {
                $q->select(['id', 'e_newspaper_id', 'section', 'position', 'image'])->orderBy('section')->orderBy('position');
            }])
            ->withCount('items')
            ->orderByDesc('issue_date')
            ->paginate(16);

        return view('frontend.e-newspapers.index', compact('issues'));
    }

    public function show(Request $request, string $slug)
    {
        $issue = $this->resolveIssue($request, $slug);

        $grouped = $issue->items->groupBy('section');

        return view('frontend.e-newspapers.show', [
            'issue' => $issue,
            'sections' => $grouped,
        ]);
    }

    public function print(Request $request, string $slug)
    {
        $issue = $this->resolveIssue($request, $slug);
        $sections = $issue->items->groupBy('section');

        return view('frontend.e-newspapers.print', [
            'issue' => $issue,
            'sections' => $sections,
        ]);
    }

    private function resolveIssue(Request $request, string $slug): ENewspaper
    {
        $allowPreview = auth()->check() && str_starts_with((string) $request->path(), 'e-gazete') && $request->boolean('preview');

        return ENewspaper::query()
            ->where('slug', $slug)
            ->when(!$allowPreview, fn ($q) => $q->where('status', 'published'))
            ->with(['items', 'items.post:id,content'])
            ->firstOrFail();
    }
}
