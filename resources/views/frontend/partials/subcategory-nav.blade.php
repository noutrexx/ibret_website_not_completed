@if(($category->children ?? collect())->isNotEmpty())
    <section class="mb-4 bg-white border rounded-lg p-3">
        <div class="text-xs uppercase tracking-wide text-gray-500 mb-2">Alt Kategoriler</div>
        <div class="flex flex-wrap gap-2">
            @foreach($category->children as $subCategory)
                <a href="{{ $subCategory->frontend_url }}" class="px-3 py-1.5 rounded text-sm border bg-white text-gray-700 border-gray-300 hover:bg-gray-50">
                    {{ $subCategory->name }}
                </a>
            @endforeach
        </div>
    </section>
@endif
