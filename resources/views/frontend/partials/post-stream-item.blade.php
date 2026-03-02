@php
    $mainCategory = $post->category?->parent ?: $post->category;
    $subCategory = $post->category?->parent ? $post->category : null;
    $shareUrl = urlencode($post->frontend_url);
    $shareTitle = urlencode($post->title);
    $streamComments = $approvedComments ?? collect();
    $streamRelated = $related ?? collect();
@endphp

<div style="display:grid;grid-template-columns:minmax(0,800px) 240px;gap:20px;align-items:start;margin-top:16px;">
    <div>
        <article class="bg-white border rounded-lg stream-post" data-post-id="{{ $post->id }}" data-post-url="{{ $post->frontend_url }}" data-post-title="{{ e($post->title) }}" style="padding:22px;">
            <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:#6b7280;">
                <a href="/" style="text-decoration:none;color:inherit;">Anasayfa</a>
                <span>&rsaquo;</span>
                @if($mainCategory)
                    <a href="{{ $mainCategory->frontend_url }}" style="text-decoration:none;color:#111;font-weight:600;">{{ $mainCategory->name }}</a>
                @else
                    <span style="color:#111;font-weight:600;">Genel</span>
                @endif
                @if($subCategory)
                    <span>&rsaquo;</span>
                    <a href="{{ $subCategory->frontend_url }}" style="text-decoration:none;color:#111;font-weight:600;">{{ $subCategory->name }}</a>
                @endif
            </div>

            <h2 style="font-size:32px;line-height:1.2;font-weight:800;color:#111;margin-top:12px;">{{ $post->title }}</h2>

            <div style="display:flex;flex-wrap:wrap;gap:14px;margin-top:14px;align-items:flex-start;">
                <div style="flex:0 0 230px;border:1px solid #e5e7eb;border-radius:12px;padding:12px;background:#f9fafb;">
                    <div style="font-size:12px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.04em;margin-bottom:8px;">Paylas</div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                        <a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" style="text-decoration:none;border:1px solid #e5e7eb;border-radius:8px;padding:8px 10px;font-size:12px;color:#111;text-align:center;background:#fff;">Facebook</a>
                        <a target="_blank" href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareTitle }}" style="text-decoration:none;border:1px solid #e5e7eb;border-radius:8px;padding:8px 10px;font-size:12px;color:#111;text-align:center;background:#fff;">X</a>
                        <a target="_blank" href="https://wa.me/?text={{ $shareTitle }}%20{{ $shareUrl }}" style="text-decoration:none;border:1px solid #e5e7eb;border-radius:8px;padding:8px 10px;font-size:12px;color:#111;text-align:center;background:#fff;">WhatsApp</a>
                        <a target="_blank" href="https://t.me/share/url?url={{ $shareUrl }}&text={{ $shareTitle }}" style="text-decoration:none;border:1px solid #e5e7eb;border-radius:8px;padding:8px 10px;font-size:12px;color:#111;text-align:center;background:#fff;">Telegram</a>
                    </div>

                    @if($post->user)
                        <div style="font-size:12px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.04em;margin:12px 0 8px;">Yazar</div>
                        <div style="display:flex;gap:10px;align-items:flex-start;">
                            <img src="{{ $post->user->avatar ? asset('storage/' . $post->user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($post->user->name) }}"
                                alt="{{ $post->user->name }}"
                                style="width:44px;height:44px;border-radius:999px;object-fit:cover;border:1px solid #e5e7eb;">
                            <div>
                                <div style="font-weight:700;color:#111;line-height:1.2;">{{ $post->user->name }}</div>
                                @if($post->user->bio)
                                    <div style="font-size:13px;color:#6b7280;line-height:1.45;margin-top:4px;">{{ $post->user->bio }}</div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <div style="flex:1 1 420px;">
                    <div style="display:flex;flex-wrap:wrap;align-items:center;gap:10px;color:#6b7280;font-size:13px;">
                        <span>{{ $post->published_at?->format('d.m.Y H:i') ?? $post->created_at?->format('d.m.Y H:i') }}</span>
                    </div>

                    @if($post->summary)
                        <p style="margin-top:12px;font-size:17px;line-height:1.55;color:#374151;border-left:4px solid #e5e7eb;padding-left:12px;">
                            {{ $post->summary }}
                        </p>
                    @endif
                </div>
            </div>

            @if($post->image)
                <figure style="margin-top:18px;">
                    <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" style="width:100%;height:auto;border-radius:10px;display:block;">
                </figure>
            @endif

            <div class="prose max-w-none" style="margin-top:22px;">
                {!! $post->content !!}
            </div>

            @php $tagList = $post->tagList(); @endphp
            @if(!empty($tagList))
                <div style="margin-top:22px;">
                    <div style="font-size:12px;font-weight:700;letter-spacing:.04em;color:#6b7280;text-transform:uppercase;margin-bottom:10px;">Etiketler</div>
                    <div style="display:flex;flex-wrap:wrap;gap:8px;">
                        @foreach($tagList as $tag)
                            <a href="{{ route('tag.show', $tag) }}" style="font-size:12px;background:#f3f4f6;border:1px solid #e5e7eb;border-radius:999px;padding:5px 11px;text-decoration:none;color:#111;">#{{ $tag }}</a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div style="margin-top:28px;padding-top:8px;border-top:1px solid #e5e7eb;">
                <div style="font-size:20px;font-weight:800;color:#111;margin-bottom:14px;">
                    Yorumlar ({{ $streamComments->count() }})
                </div>

                @if($streamComments->isNotEmpty())
                    <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:18px;">
                        @foreach($streamComments as $comment)
                            <div style="border:1px solid #e5e7eb;border-radius:10px;padding:12px;background:#fafafa;">
                                <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;">
                                    <strong style="font-size:14px;color:#111;">{{ $comment->name }}</strong>
                                    <span style="font-size:12px;color:#6b7280;">{{ ($comment->approved_at ?? $comment->created_at)?->format('d.m.Y H:i') }}</span>
                                </div>
                                <p style="margin:8px 0 0;font-size:14px;line-height:1.5;color:#374151;">{{ $comment->content }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p style="font-size:14px;color:#6b7280;margin-bottom:16px;">Henuz onaylanmis yorum yok.</p>
                @endif

                <form method="POST" action="{{ route('post.comment.store', ['categorySlug' => $mainCategory?->slug ?? 'genel', 'slugKey' => $post->slug . '-n' . $post->id]) }}" style="border:1px solid #e5e7eb;border-radius:12px;padding:14px;background:#fff;">
                    @csrf
                    <div style="display:grid;grid-template-columns:1fr;gap:10px;">
                        <input type="text" name="name" placeholder="Adiniz" maxlength="80" required style="height:40px;border:1px solid #d1d5db;border-radius:8px;padding:0 12px;font-size:14px;">
                        <textarea name="content" rows="4" placeholder="Yorumunuz" maxlength="2000" required style="border:1px solid #d1d5db;border-radius:8px;padding:10px 12px;font-size:14px;"></textarea>
                    </div>
                    <div style="font-size:12px;color:#6b7280;margin-top:8px;">Yorumunuz editor onayindan sonra yayinlanir.</div>
                    <button type="submit" style="margin-top:10px;height:40px;padding:0 16px;border:0;border-radius:8px;background:#111827;color:#fff;font-weight:700;">Yorum Gonder</button>
                </form>
            </div>

            <div class="post-autonext-anchor" style="height:1px;"></div>
        </article>
    </div>

    <aside>
        @include('frontend.partials.post-related-sidebar', ['related' => $streamRelated])

        <div style="margin-top:12px;">
            <div style="height:250px;border:1px dashed #cbd5e1;border-radius:10px;background:#f8fafc;display:flex;align-items:center;justify-content:center;color:#64748b;font-size:13px;">320x250 Reklam Alani</div>
            <div style="height:280px;border:1px dashed #cbd5e1;border-radius:10px;background:#f8fafc;display:flex;align-items:center;justify-content:center;color:#64748b;font-size:13px;margin-top:10px;">320x280 Reklam Alani</div>
        </div>
    </aside>
</div>
