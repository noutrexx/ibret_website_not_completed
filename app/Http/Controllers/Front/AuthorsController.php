<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\User;

class AuthorsController extends Controller
{
    public function index()
    {
        $ttl = (int) setting('cache_ttl_authors', 300);
        $ttl = $ttl > 0 ? $ttl : 300;
        $expiresAt = now()->addSeconds($ttl);

        $authors = cache()->remember('authors:page:1', $expiresAt, function () {
            return User::query()
                ->select(['id', 'name', 'author_name', 'avatar', 'bio', 'role'])
                ->whereHas('articles', function ($q) {
                    $q->where('status', 'published');
                })
                ->with(['articles' => function ($q) {
                    $q->select(['id', 'user_id', 'category_id', 'title', 'slug', 'summary', 'content', 'published_at'])
                        ->where('status', 'published')
                        ->with(['category:id,name,slug'])
                        ->orderByDesc('published_at')
                        ->take(1);
                }])
                ->orderBy('author_name')
                ->orderBy('name')
                ->get();
        });

        return view('frontend.authors', [
            'authors' => $authors,
            'seoTitle' => 'Yazarlar | ' . setting('site_title'),
            'seoDescription' => 'Yazar kadromuz ve son makaleler.',
        ]);
    }
}
