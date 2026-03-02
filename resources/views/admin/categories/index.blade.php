@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success shadow-sm border-0">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger shadow-sm border-0">{{ session('error') }}</div>
    @endif

    <div class="d-flex justify-content-between mb-4">
        <h3>Kategoriler</h3>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Kategori Ekle</a>
    </div>

    <div class="card shadow border-0">
        <div class="card-body p-0">
            <table class="table table-hover align-middle">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>İçerik (Kategori Adı)</th>
                        <th>Üst Kategori</th>
                        <th>Sıralama</th>
                        <th class="text-center">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $cat)
                    <tr>
                        <td class="ps-4">{{ $cat->id }}</td>
                        <td>
                            @if($cat->parent_id) — @endif <strong>{{ $cat->name }}</strong>
                        </td>
                        <td>
                            <span class="badge bg-info text-white">
                                {{ $cat->parent->name ?? 'Ana Kategori' }}
                            </span>
                        </td>
                        <td>{{ $cat->order }}</td>
                        <td class="text-center">
                            <a href="{{ route('admin.categories.edit', $cat->id) }}" class="btn btn-sm btn-outline-warning" title="Duzenle">
                                <i class="fa fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.categories.destroy', $cat->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Sil" onclick="return confirm('Bu kategoriyi silmek istiyor musunuz?')">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
