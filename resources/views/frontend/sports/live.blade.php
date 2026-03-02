@extends('frontend.layouts.app')

@section('content')
    <div style="width:1060px;max-width:100%;margin:0 auto;">
        <section class="mb-6 bg-white border rounded-lg p-6">
            <div class="text-xs uppercase tracking-wide text-gray-500">Spor</div>
            <h1 class="text-3xl font-semibold mt-1">Canli Skor</h1>
            <div class="text-sm text-gray-500 mt-2">Canli skor bu kurulumda kapali.</div>
        </section>

        @include('frontend.partials.subcategory-nav', ['category' => $category])

        <section class="bg-white border rounded-lg overflow-hidden mb-6">
            <div class="px-4 py-3 border-b font-semibold">Durum</div>
            <div class="p-4 text-sm text-gray-600">
                Canli skor taleplerini kaldirdik. Spor verileri TheSportsDB uzerinden sadece puan durumu ve fikstur olarak alinacak.
            </div>
        </section>
    </div>
@endsection
