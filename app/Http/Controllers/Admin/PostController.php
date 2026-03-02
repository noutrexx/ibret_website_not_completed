<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePostRequest;
use App\Http\Requests\Admin\UpdatePostRequest;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::news()
            ->select(['id', 'title', 'slug', 'image', 'category_id', 'user_id', 'type', 'is_breaking', 'view_count', 'created_at'])
            ->with(['category:id,name,slug,parent_id', 'category.parent:id,name', 'user:id,name'])
            ->filter($request->only(['search', 'category', 'type']));

        $posts = $query->latest()->paginate(20)->withQueryString();
        $categories = Category::with('parent:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id']);

        return view('admin.posts.index', compact('posts', 'categories'));
    }

    public function create()
    {
        $categoryPicker = $this->buildCategoryPickerData();

        return view('admin.posts.create', compact('categoryPicker'));
    }

    public function store(StorePostRequest $request)
    {
        $data = $request->validated();

        $post = new Post();
        $post->title = $data['title'];
        $post->slug = Post::uniqueSlug($data['title']);
        $post->summary = $data['summary'] ?? null;
        $post->tags = Post::normalizeTags($data['tags'] ?? null);
        $post->content = $data['content'];
        $post->category_id = (int) $data['category_id'];
        $post->user_id = auth()->id();
        $post->type = $data['type'];
        $post->is_breaking = (bool) ($data['is_breaking'] ?? false);
        $post->photo_gallery = $this->normalizeMediaList($data['photo_gallery'] ?? []);
        $post->video_gallery = $this->normalizeMediaList($data['video_gallery'] ?? []);
        $post->city = $data['city'] ?? null;
        $post->published_at = $data['published_at'] ?? now();
        $post->content_kind = 'news';
        $post->status = 'published';

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $post->slug . '.' . $file->getClientOriginalExtension();
            $post->image = $file->storeAs('posts', $filename, 'public');
        } elseif (!empty($data['image_path'])) {
            $post->image = ltrim((string) $data['image_path'], '/');
        }

        $post->save();

        return redirect()->route('admin.posts.index')
            ->with('success', 'Haber başarıyla yayınlandı.');
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();

        return back()->with('success', 'Haber çöp kutusuna taşındı.');
    }

    public function edit($id)
    {
        $post = Post::findOrFail($id);
        $categoryPicker = $this->buildCategoryPickerData($post->category_id);

        return view('admin.posts.edit', compact('post', 'categoryPicker'));
    }

    public function update(UpdatePostRequest $request, $id)
    {
        $post = Post::findOrFail($id);
        $data = $request->validated();

        $post->title = $data['title'];
        $post->slug = Post::uniqueSlug($data['title'], $post->id);
        $post->summary = $data['summary'] ?? null;
        $post->tags = Post::normalizeTags($data['tags'] ?? null);
        $post->content = $data['content'];
        $post->category_id = (int) $data['category_id'];
        $post->type = $data['type'];
        $post->is_breaking = (bool) ($data['is_breaking'] ?? false);
        $post->photo_gallery = $this->normalizeMediaList($data['photo_gallery'] ?? []);
        $post->video_gallery = $this->normalizeMediaList($data['video_gallery'] ?? []);
        $post->city = $data['city'] ?? null;
        $post->published_at = $data['published_at'] ?? $post->published_at;

        if ($request->hasFile('image')) {
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            $post->image = $request->file('image')->store('posts', 'public');
        } elseif (!empty($data['image_path'])) {
            $post->image = ltrim((string) $data['image_path'], '/');
        }

        $post->save();

        return redirect()->route('admin.posts.index')->with('success', 'Haber başarıyla güncellendi.');
    }

    public function trashed()
    {
        $posts = Post::news()
            ->onlyTrashed()
            ->select(['id', 'title', 'category_id', 'deleted_at'])
            ->with('category:id,name')
            ->latest()
            ->paginate(20);

        return view('admin.posts.trashed', compact('posts'));
    }

    public function restore($id)
    {
        $post = Post::news()->onlyTrashed()->findOrFail($id);
        $post->restore();

        return redirect()->route('admin.posts.trashed')->with('success', 'Haber başarıyla geri yüklendi.');
    }

    public function forceDelete($id)
    {
        $post = Post::news()->onlyTrashed()->findOrFail($id);

        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }

        $post->forceDelete();

        return redirect()->route('admin.posts.trashed')->with('success', 'Haber kalıcı olarak silindi.');
    }

    private function normalizeMediaList(array $items): ?array
    {
        $normalized = collect($items)
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return empty($normalized) ? null : $normalized;
    }

    private function buildCategoryPickerData(?int $selectedCategoryId = null): array
    {
        $parents = Category::query()
            ->select(['id', 'name', 'slug'])
            ->with(['children:id,parent_id,name,slug'])
            ->whereNull('parent_id')
            ->orderBy('order', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        $selected = null;
        if ($selectedCategoryId) {
            $selected = Category::with('parent:id,name')->find($selectedCategoryId);
        }

        return [
            'parents' => $parents,
            'selected_parent_id' => $selected?->parent_id ? (int) $selected->parent_id : (int) ($selected?->id ?? 0),
            'selected_child_id' => $selected?->parent_id ? (int) $selected->id : 0,
        ];
    }
}
