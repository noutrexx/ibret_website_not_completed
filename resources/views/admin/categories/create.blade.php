@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card shadow border-0 col-md-8 mx-auto">
        <div class="card-header bg-white fw-bold">Yeni Kategori Oluştur</div>
        <div class="card-body">
            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-bold small">Kategori Seçiniz (Üst Kategori)</label>
                    <select name="parent_id" class="form-select">
                        <option value="">-- ANA KATEGORİ OLSUN --</option>
                        @foreach($categories as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Kategori Adı</label>
                    <input type="text" name="name" class="form-control" required placeholder="Örn: Spor">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Sayfa Başlığı (SEO)</label>
                    <input type="text" name="page_title" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Sayfa Açıklama</label>
                    <textarea name="page_description" class="form-control" rows="2"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Sayfa Etiketleri (Keywords)</label>
                    <input type="text" name="page_keywords" class="form-control" placeholder="etiket, haber, kategori">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Sıralama</label>
                    <input type="number" name="order" class="form-control" value="0">
                </div>

                <hr>
                <button type="submit" class="btn btn-success w-100 py-2 fw-bold">KATEGORİYİ KAYDET</button>
            </form>
        </div>
    </div>
</div>
@endsection