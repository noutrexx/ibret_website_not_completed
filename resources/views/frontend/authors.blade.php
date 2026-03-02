@extends('frontend.layouts.app')

@section('content')
    <div style="width:1060px;max-width:100%;margin:0 auto;">
        <section style="margin-top:24px;width:1060px;max-width:100%;position:relative;">
            <div class="border rounded-lg overflow-hidden relative" style="background:#c90914;">
                <svg xmlns="http://www.w3.org/2000/svg" width="1060" height="319.586" viewBox="0 0 1060 319.586" style="position:absolute;left:0;top:0;width:1060px;height:319.586px;z-index:0;">
                    <path d="M14,491H1074V602.441L14,810.586Z" transform="translate(-14 -491)" fill="#c90914"></path>
                </svg>
                <div style="position:relative;z-index:1;padding:22px 22px 20px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;color:#fff;">
                        <div style="font-size:18px;font-weight:700;">Yazarlarimizin Kaleminden</div>
                        <div style="font-size:12px;letter-spacing:.08em;text-transform:uppercase;">Tum Yazarlar</div>
                    </div>

                    @if(($authors ?? collect())->isNotEmpty())
                        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;">
                            @foreach($authors as $author)
                                @php
                                    $authorName = $author->author_name ?: ($author->name ?? 'Yazar');
                                    $authorBio = $author->bio ?: 'Yeni yazilar burada.';
                                    $post = $author->articles->first();
                                    $excerpt = $post?->summary ?: \Illuminate\Support\Str::limit(strip_tags($post?->content ?? ''), 140);
                                    $initial = mb_strtoupper(mb_substr($authorName, 0, 1, 'UTF-8'), 'UTF-8');
                                @endphp
                                <div style="background:#fff;border-radius:10px;overflow:hidden;border:1px solid rgba(255,255,255,.35);">
                                    <div style="display:flex;gap:12px;padding:14px 14px 10px;border-bottom:1px solid #f1f1f1;">
                                        <div style="width:64px;height:64px;border-radius:50%;overflow:hidden;background:#f3f4f6;flex:0 0 64px;display:flex;align-items:center;justify-content:center;">
                                            @if($author->avatar)
                                                <img src="{{ asset('storage/' . $author->avatar) }}" alt="{{ $authorName }}" style="width:64px;height:64px;object-fit:cover;display:block;">
                                            @else
                                                <div style="font-weight:700;color:#6b7280;font-size:20px;">{{ $initial }}</div>
                                            @endif
                                        </div>
                                        <div>
                                            <div style="font-weight:700;color:#111;font-size:15px;line-height:1.2;">{{ $authorName }}</div>
                                            <div style="font-size:12px;color:#6b7280;margin-top:4px;line-height:1.3;">{{ $authorBio }}</div>
                                        </div>
                                    </div>
                                    <div style="padding:12px 14px 16px;">
                                        @if($post)
                                            <a href="{{ $post->frontend_url }}" style="display:block;color:#111;text-decoration:none;font-weight:600;font-size:14px;line-height:1.3;">
                                                {{ $post->title }}
                                            </a>
                                            <div style="color:#6b7280;font-size:12px;line-height:1.5;margin-top:6px;">
                                                {{ $excerpt }}
                                            </div>
                                        @else
                                            <div style="color:#6b7280;font-size:12px;">Bu yazarin makalesi bulunmuyor.</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div style="color:#fff;font-size:14px;">Henuz yazar bulunmuyor.</div>
                    @endif
                </div>
            </div>
        </section>
    </div>
@endsection


