<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreArticleRequest;
use App\Http\Requests\Admin\UpdateArticleRequest;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::articles()
            ->select(['id', 'title', 'slug', 'category_id', 'user_id', 'status', 'view_count', 'created_at'])
            ->with(['user:id,name', 'category:id,name'])
            ->filter($request->only(['search', 'category', 'status']));

        $articles = $query->latest()->paginate(20)->withQueryString();
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('admin.articles.index', compact('articles', 'categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get(['id', 'name']);
        $authors = User::whereIn('role', ['admin', 'editor', 'columnist'])
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        return view('admin.articles.create', compact('categories', 'authors'));
    }

    public function store(StoreArticleRequest $request)
    {
        $data = $request->validated();
        $status = $data['status'] ?? 'draft';

        Post::create([
            'title' => $data['title'],
            'slug' => Post::uniqueSlug($data['title']),
            'content' => $data['content'],
            'category_id' => $data['category_id'] ?? null,
            'user_id' => $data['author_id'] ?? auth()->id(),
            'status' => $status,
            'content_kind' => 'article',
            'view_count' => 0,
            'published_at' => ($status === 'published') ? now() : null,
        ]);

        return redirect()->route('admin.articles.index')
            ->with('success', 'Makale başarıyla oluşturuldu.');
    }

    public function edit($id)
    {
        $article = Post::articles()->findOrFail($id);
        $categories = Category::orderBy('name')->get(['id', 'name']);
        $authors = User::whereIn('role', ['admin', 'editor', 'columnist'])
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        return view('admin.articles.edit', compact('article', 'categories', 'authors'));
    }

    public function update(UpdateArticleRequest $request, $id)
    {
        $article = Post::articles()->findOrFail($id);
        $data = $request->validated();
        $status = $data['status'];

        $article->update([
            'title' => $data['title'],
            'slug' => Post::uniqueSlug($data['title'], $article->id),
            'content' => $data['content'],
            'category_id' => $data['category_id'] ?? null,
            'user_id' => $data['author_id'] ?? $article->user_id,
            'status' => $status,
            'published_at' => ($status === 'published')
                ? ($article->published_at ?? now())
                : null,
        ]);

        return redirect()->route('admin.articles.index')
            ->with('success', 'Makale başarıyla güncellendi.');
    }

    public function destroy($id)
    {
        $article = Post::articles()->findOrFail($id);
        $article->delete();

        return back()->with('success', 'Makale çöp kutusuna taşındı.');
    }

    public function trashed(Request $request)
    {
        $query = Post::articles()
            ->onlyTrashed()
            ->select(['id', 'title', 'slug', 'category_id', 'user_id', 'status', 'deleted_at'])
            ->with(['user:id,name', 'category:id,name'])
            ->filter($request->only(['search', 'category', 'status']));

        $articles = $query->latest('deleted_at')->paginate(20)->withQueryString();
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('admin.articles.trashed', compact('articles', 'categories'));
    }

    public function restore($id)
    {
        $article = Post::articles()->onlyTrashed()->findOrFail($id);
        $article->restore();

        return redirect()->route('admin.articles.trashed')
            ->with('success', 'Makale geri yüklendi.');
    }

    public function forceDelete($id)
    {
        $article = Post::articles()->onlyTrashed()->findOrFail($id);
        $article->forceDelete();

        return redirect()->route('admin.articles.trashed')
            ->with('success', 'Makale kalıcı olarak silindi.');
    }
}
