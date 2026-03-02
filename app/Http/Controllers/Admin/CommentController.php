<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;

class CommentController extends Controller
{
    public function index()
    {
        $status = request('status', 'pending');
        if (!in_array($status, ['pending', 'approved', 'rejected', 'all'], true)) {
            $status = 'pending';
        }

        $comments = Comment::query()
            ->with('post:id,title,slug,category_id')
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.comments.index', compact('comments', 'status'));
    }

    public function approve(int $id)
    {
        $comment = Comment::findOrFail($id);
        $comment->status = 'approved';
        $comment->approved_at = now();
        $comment->save();
        cache()->forget('post:comments:approved:' . $comment->post_id);

        return back()->with('success', 'Yorum onaylandi.');
    }

    public function reject(int $id)
    {
        $comment = Comment::findOrFail($id);
        $comment->status = 'rejected';
        $comment->approved_at = null;
        $comment->save();
        cache()->forget('post:comments:approved:' . $comment->post_id);

        return back()->with('success', 'Yorum reddedildi.');
    }

    public function destroy(int $id)
    {
        $comment = Comment::findOrFail($id);
        $postId = $comment->post_id;
        $comment->delete();
        cache()->forget('post:comments:approved:' . $postId);

        return back()->with('success', 'Yorum silindi.');
    }
}
