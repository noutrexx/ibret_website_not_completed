@foreach($posts as $index => $post)
    @php
        $img = $post->image ? asset('storage/' . $post->image) : setting('seo_og_image');
        $summary = \Illuminate\Support\Str::limit($post->summary ?: strip_tags($post->content ?? ''), 110);
    @endphp
    <article class="bg-white rounded-xl overflow-hidden mb-4 shadow-sm border border-gray-100 transition duration-200 hover:-translate-y-0.5 hover:shadow-md" style="width:100%;height:156px;">
        <a href="{{ $post->frontend_url }}" class="block" style="width:100%;height:156px;">
            <div style="display:flex;flex-direction:row;width:100%;height:156px;">
                <div style="width:196px;height:156px;flex:0 0 196px;position:relative;background:#f3f4f6;">
                    @if($img)
                        <img src="{{ $img }}" alt="{{ $post->title }}" style="width:196px;height:156px;object-fit:cover;display:block;">
                    @endif
                    <div style="position:absolute;left:10px;top:10px;">
                        <span style="display:inline-flex;align-items:center;height:22px;padding:0 8px;border-radius:999px;background:rgba(17,24,39,.72);backdrop-filter:blur(4px);color:#fff;font-size:10px;font-weight:700;letter-spacing:.03em;text-transform:uppercase;">
                            {{ $post->category?->name ?? 'Genel' }}
                        </span>
                    </div>
                </div>
                <div style="flex:1 1 auto;height:156px;padding:14px 16px;display:flex;flex-direction:column;">
                    <h3 class="text-[15px] font-semibold leading-snug text-gray-900 line-clamp-2">{{ $post->title }}</h3>
                    <p class="text-sm text-gray-600 mt-2" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;max-height:40px;">
                        {{ $summary }}
                    </p>
                    <div class="mt-auto flex items-center gap-2 text-xs text-gray-400">
                        <span style="width:4px;height:4px;border-radius:999px;background:#ef4444;display:inline-block;"></span>
                        <span>
                        {{ $post->published_at?->format('d.m.Y H:i') ?? $post->created_at?->format('d.m.Y H:i') }}
                        </span>
                    </div>
                </div>
            </div>
        </a>
    </article>
@endforeach


