<section class="mb-4 bg-white border rounded-lg p-3">
    <div class="flex flex-wrap gap-2">
        <a href="{{ $category->frontend_url }}"
           class="px-3 py-2 rounded text-sm border {{ ($active ?? '') === 'home' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 border-gray-300' }}">
            Ekonomi Ana
        </a>
        <a href="{{ route('category.economy.currencies', $category->slug) }}"
           class="px-3 py-2 rounded text-sm border {{ ($active ?? '') === 'currencies' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 border-gray-300' }}">
            Doviz
        </a>
        <a href="{{ route('category.economy.gold', $category->slug) }}"
           class="px-3 py-2 rounded text-sm border {{ ($active ?? '') === 'gold' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 border-gray-300' }}">
            Altin
        </a>
        <a href="{{ route('category.economy.crypto', $category->slug) }}"
           class="px-3 py-2 rounded text-sm border {{ ($active ?? '') === 'crypto' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 border-gray-300' }}">
            Kripto
        </a>
    </div>
</section>
