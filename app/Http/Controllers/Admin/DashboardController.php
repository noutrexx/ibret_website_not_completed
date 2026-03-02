<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_posts' => Post::count(),
            'total_category' => Category::count(),
            'total_views' => Post::sum('view_count'),
            'total_users' => User::count(),
        ];

        $recent_posts = Post::select(['id', 'title', 'category_id', 'created_at'])
            ->with('category:id,name')
            ->latest()
            ->take(5)
            ->get();

        $popular_posts = Post::select(['id', 'title', 'view_count'])
            ->orderBy('view_count', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recent_posts', 'popular_posts'));
    }
}
