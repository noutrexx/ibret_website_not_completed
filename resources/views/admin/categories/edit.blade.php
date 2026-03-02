@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card shadow border-0 col-md-8 mx-auto">
        <div class="card-header bg-white fw-bold">Kategori Duzenle</div>
        <div class="card-body">
            <form action="{{ route('admin.categories.update', $category->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label fw-bold small">Ust Kategori</label>
                    <select name="parent_id" class="form-select">
                        <option value="">-- ANA KATEGORI OLSUN --</option>
                        @foreach($categories as $parent)
                            <option value="{{ $parent->id }}" @selected((int) old('parent_id', $category->parent_id) === (int) $parent->id)>
                                {{ $parent->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('parent_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Kategori Adi</label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name', $category->name) }}">
                    @error('name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Sayfa Basligi (SEO)</label>
                    <input type="text" name="page_title" class="form-control" value="{{ old('page_title', $category->page_title) }}">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Sayfa Aciklama</label>
                    <textarea name="page_description" class="form-control" rows="2">{{ old('page_description', $category->page_description) }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Sayfa Etiketleri (Keywords)</label>
                    <input type="text" name="page_keywords" class="form-control" value="{{ old('page_keywords', $category->page_keywords) }}">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Siralama</label>
                    <input type="number" name="order" class="form-control" value="{{ old('order', $category->order ?? 0) }}">
                </div>

                <hr>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary w-50">Vazgec</a>
                    <button type="submit" class="btn btn-success w-50 fw-bold">GUNCELLE</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
