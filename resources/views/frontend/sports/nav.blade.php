<section class="mb-6 bg-white border rounded-lg p-3">
    @php $leagueParam = ['league' => ($currentLeagueKey ?? null)]; @endphp
    <div class="flex flex-wrap gap-2">
        <a href="{{ $category->frontend_url }}{{ !empty($leagueParam['league']) ? ('?league=' . urlencode($leagueParam['league'])) : '' }}"
           class="px-3 py-2 rounded text-sm border {{ ($active ?? '') === 'home' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 border-gray-300' }}">
            Spor Ana
        </a>
        <a href="{{ route('category.sports.standings', ['slug' => $category->slug] + $leagueParam) }}"
           class="px-3 py-2 rounded text-sm border {{ ($active ?? '') === 'standings' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 border-gray-300' }}">
            Puan Durumu
        </a>
        <a href="{{ route('category.sports.fixtures', ['slug' => $category->slug] + $leagueParam) }}"
           class="px-3 py-2 rounded text-sm border {{ ($active ?? '') === 'fixtures' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 border-gray-300' }}">
            Fikstur
        </a>
        <a href="{{ route('category.sports.live', ['slug' => $category->slug] + $leagueParam) }}"
           class="px-3 py-2 rounded text-sm border {{ ($active ?? '') === 'live' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 border-gray-300' }}">
            Canli Skor
        </a>
    </div>
</section>
