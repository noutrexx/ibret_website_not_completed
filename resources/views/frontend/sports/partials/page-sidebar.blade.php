<aside style="width:320px;">
    @unless(($hideSourceBox ?? false) === true)
        <section class="mb-4 bg-white border rounded-lg p-4">
            <div class="text-xs uppercase tracking-wide text-gray-500">Veri Kaynagi</div>
            <div class="text-sm font-semibold text-gray-900 mt-1">Ozel Spor API</div>
            @if(!empty($currentLeagueName))
                <div class="text-xs text-gray-500 mt-1">{{ $currentLeagueName }}</div>
            @endif
        </section>
    @endunless

    <section class="mb-4">
        <div class="text-sm font-semibold text-gray-700 mb-2">Haftanin En Cok Okunanlari</div>
        <div class="space-y-4">
            @forelse(($weeklyPopularPosts ?? collect()) as $post)
                <a href="{{ $post->frontend_url }}" class="block border rounded-lg overflow-hidden relative" style="width:100%;height:220px;">
                    @if($post->image)
                        <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" style="width:100%;height:220px;object-fit:cover;display:block;">
                    @endif
                    <div style="position:absolute;left:0;right:0;bottom:0;height:90px;background:linear-gradient(to top, rgba(0,0,0,.72), rgba(0,0,0,0));"></div>
                    <div style="position:absolute;left:14px;right:14px;bottom:12px;color:#fff;font-size:15px;font-weight:600;line-height:1.3;">
                        {{ $post->title }}
                    </div>
                </a>
            @empty
                <div class="text-sm text-gray-500 bg-white border rounded-lg p-4">Bu hafta icin populer haber bulunamadi.</div>
            @endforelse
        </div>
    </section>

    <section class="space-y-5" style="margin-top:16px;">
        <div class="border bg-white rounded-lg flex items-center justify-center text-sm text-gray-400" style="height:100px;">
            320x100 Header Alti Reklam
        </div>
        <div class="border bg-white rounded-lg flex items-center justify-center text-sm text-gray-400" style="height:250px;">
            320x250 Sidebar Reklam
        </div>
        <div class="border bg-white rounded-lg flex items-center justify-center text-sm text-gray-400" style="height:600px;">
            300x600 Skyscraper Reklam
        </div>
    </section>
</aside>
