@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fa fa-trash-can text-danger me-2"></i> Çöp Kutusu</h3>
        <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary btn-sm">Haber Listesine Dön</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="px-4">ID</th>
                        <th>BAŞLIK</th>
                        <th>SİLİNME TARİHİ</th>
                        <th class="text-center">İŞLEMLER</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($posts as $post)
                    <tr>
                        <td class="px-4">#{{ $post->id }}</td>
                        <td>
                            <div class="fw-bold">{{ $post->title }}</div>
                            <small class="text-muted">{{ $post->category->name ?? 'Kategorisiz' }}</small>
                        </td>
                        <td>{{ $post->deleted_at->format('d.m.Y H:i') }}</td>
                        <td class="text-center">
                            <a href="{{ route('admin.posts.restore', $post->id) }}" class="btn btn-sm btn-success" title="Geri Yükle">
                                <i class="fa fa-undo"></i> Geri Yükle
                            </a>

                            <form action="{{ route('admin.posts.forceDelete', $post->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('DİKKAT! Bu haber kalıcı olarak silinecektir. Emin misiniz?')" title="Kalıcı Sil">
                                    <i class="fa fa-times"></i> Kalıcı Sil
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">Çöp kutusu boş.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection