@php
    $img = $post->image ? asset('storage/' . $post->image) : setting('seo_og_image');
@endphp

<article class="bg-white rounded-lg border overflow-hidden hover:shadow-sm transition-shadow">
    <a href="{{ $post->frontend_url }}">
        @if($img)
            <img src="{{ $img }}" alt="{{ $post->title }}" class="w-full h-48 object-cover">
        @endif
        <div class="p-4">
            <div class="text-xs text-gray-500 mb-2 uppercase tracking-wide">
                <span>{{ $post->category?->name ?? 'Genel' }}</span>
            </div>
            <h3 class="font-semibold leading-snug line-clamp-2">{{ $post->title }}</h3>
            <div class="text-xs text-gray-400 mt-2">
                {{ $post->published_at?->format('d.m.Y H:i') ?? $post->created_at?->format('d.m.Y H:i') }}
            </div>
        </div>
    </a>
</article>


