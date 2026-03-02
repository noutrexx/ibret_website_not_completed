@extends('frontend.layouts.app')

@section('content')
    <section class="mb-6">
        <h1 class="text-2xl font-semibold">Arama</h1>
        <p class="text-gray-600 mt-2">"{{ $query }}" için sonuçlar</p>
    </section>

    <section class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($posts as $post)
            @include('frontend.partials.post-card', ['post' => $post])
        @endforeach
    </section>

    <div class="mt-6">
        {{ $posts->links() }}
    </div>
@endsection