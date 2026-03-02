<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, string $categorySlug, string $slugKey)
    {
        $parsed = $this->parseSlugKey($slugKey);
        abort_if(!$parsed, 404);

        [$slug, $id] = $parsed;

        $post = Post::where('id', $id)
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'content' => ['required', 'string', 'min:3', 'max:2000'],
        ], [
            'name.required' => 'Ad alani zorunludur.',
            'content.required' => 'Yorum alani zorunludur.',
        ]);

        Comment::create([
            'post_id' => $post->id,
            'name' => trim((string) $validated['name']),
            'content' => trim((string) $validated['content']),
            'status' => 'pending',
        ]);
        cache()->forget('post:comments:approved:' . $post->id);

        return back()->with('comment_success', 'Yorumunuz alindi. Onaylandiktan sonra yayina girecektir.');
    }

    protected function parseSlugKey(string $slugKey): ?array
    {
        if (!preg_match('/^(.*)-n(\d+)$/', $slugKey, $m)) {
            return null;
        }

        $slug = trim((string) ($m[1] ?? ''));
        $id = (int) ($m[2] ?? 0);

        if ($slug === '' || $id <= 0) {
            return null;
        }

        return [$slug, $id];
    }
}
