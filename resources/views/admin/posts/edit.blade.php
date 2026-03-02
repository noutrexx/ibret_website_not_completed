@extends('layouts.admin')

@section('content')
@php
    $selectedCategoryId = (int) old('category_id', $post->category_id);
    $parentOptions = $categoryPicker['parents'];
    $childMap = $parentOptions->mapWithKeys(function ($parent) {
        return [(string) $parent->id => $parent->children->map(fn ($child) => ['id' => $child->id, 'name' => $child->name])->values()];
    });
@endphp

<div class="container-fluid">
    @if($errors->any())
        <div class="alert alert-danger shadow-sm border-0">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.posts.update', $post->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="category_id" id="category_id" value="{{ $selectedCategoryId }}">
        <input type="hidden" name="image_path" id="cover_image_path" value="{{ old('image_path', $post->image) }}">

        <div class="row">
            <div class="col-md-9">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="fw-bold mb-2">Baslik</label>
                            <input type="text" name="title" class="form-control form-control-lg" value="{{ old('title', $post->title) }}" required>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label class="fw-bold mb-2 small">Ana Kategori</label>
                                <select id="parent_category_select" class="form-select" required>
                                    <option value="">Secin...</option>
                                    @foreach($parentOptions as $parent)
                                        <option value="{{ $parent->id }}" @selected((int) old('parent_category_id', $categoryPicker['selected_parent_id']) === (int) $parent->id)>
                                            {{ $parent->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="fw-bold mb-2 small">Alt Kategori (Opsiyonel)</label>
                                <select id="child_category_select" class="form-select">
                                    <option value="">Alt kategori yok</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="fw-bold mb-2 small">Icerik Turu</label>
                                <select name="type" class="form-select" required>
                                    @foreach(['normal', 'manset', 'surmanset', 'top_manset', 'spor_manset', 'ekonomi_manset', 'gizli'] as $type)
                                        <option value="{{ $type }}" @selected(old('type', $post->type) === $type)>{{ strtoupper($type) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="fw-bold mb-2 small">Yayin Tarihi</label>
                                <input type="datetime-local" name="published_at" class="form-control" value="{{ old('published_at', optional($post->published_at)->format('Y-m-d\\TH:i')) }}">
                            </div>
                            <div class="col-md-2">
                                <label class="fw-bold mb-2 small">Sehir</label>
                                <input type="text" name="city" class="form-control" value="{{ old('city', $post->city) }}" placeholder="Opsiyonel">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="fw-bold mb-2">Ozet</label>
                            <textarea name="summary" class="form-control" rows="3">{{ old('summary', $post->summary) }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label class="fw-bold mb-2">Etiketler</label>
                            <input type="text" name="tags" class="form-control" value="{{ old('tags', $post->tags) }}" placeholder="Virgul ile ayirin (or: spor,futbol,turkiye)">
                        </div>

                        @include('admin.components.rich-editor-field', [
                            'label' => 'Icerik',
                            'name' => 'content',
                            'id' => 'editor',
                            'value' => old('content', $post->content),
                            'rows' => 12,
                            'required' => true,
                            'wrapperClass' => 'mb-0',
                        ])
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white fw-bold small text-secondary">KAPAK RESMI</div>
                    <div class="card-body text-center">
                        <div id="cover_image_preview_wrap" class="mb-2 {{ old('image_path', $post->image) ? '' : 'd-none' }}">
                            <img id="cover_image_preview" src="{{ old('image_path', $post->image) ? asset('storage/' . ltrim(old('image_path', $post->image), '/')) : '' }}" class="img-fluid rounded border" alt="post-image">
                        </div>
                        <div class="d-grid gap-2 mb-2 text-start">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="open_cover_media_picker"><i class="fa fa-images me-1"></i> Kutuphaneden Sec</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="clear_cover_media_picker"><i class="fa fa-times me-1"></i> Secimi Temizle</button>
                        </div>
                        <small class="text-muted d-block text-start">Kapak resmi sadece medya kutuphanesinden secilir.</small>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white fw-bold small text-secondary">YAYIN OZELLIKLERI</div>
                    <div class="card-body py-2">
                        <div class="form-check form-switch my-2">
                            <input class="form-check-input" type="checkbox" name="is_breaking" id="is_breaking" value="1" @checked(old('is_breaking', $post->is_breaking))>
                            <label class="form-check-label small" for="is_breaking">Son Dakika</label>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white fw-bold small text-secondary">FOTO GALERI</div>
                    <div class="card-body">
                        <div id="photo-gallery-list"></div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-photo-gallery-item">
                            <i class="fa fa-plus me-1"></i> Foto URL Ekle
                        </button>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white fw-bold small text-secondary">VIDEO GALERI</div>
                    <div class="card-body">
                        <div id="video-gallery-list"></div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-video-gallery-item">
                            <i class="fa fa-plus me-1"></i> Video URL Ekle
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-warning w-100 py-3 fw-bold text-white">Degisiklikleri Kaydet</button>
            </div>
        </div>
    </form>
</div>

@include('admin.media._modal')
@include('admin.media._manager_script')

<script>
    const childMap = @json($childMap);
    const parentSelect = document.getElementById('parent_category_select');
    const childSelect = document.getElementById('child_category_select');
    const categoryInput = document.getElementById('category_id');
    const selectedChildId = {{ (int) old('child_category_id', $categoryPicker['selected_child_id']) }};
    const selectedCategoryId = {{ $selectedCategoryId }};
    const preferredChildId = selectedChildId || selectedCategoryId;

    function renderChildOptions(parentId) {
        childSelect.innerHTML = '<option value="">Alt kategori yok</option>';
        const children = childMap[parentId] || [];
        for (const child of children) {
            const option = document.createElement('option');
            option.value = child.id;
            option.textContent = child.name;
            if (Number(child.id) === Number(preferredChildId)) option.selected = true;
            childSelect.appendChild(option);
        }
        childSelect.disabled = children.length === 0;
    }

    function syncCategoryId() {
        categoryInput.value = childSelect.value || parentSelect.value || '';
    }

    parentSelect.addEventListener('change', () => { renderChildOptions(parentSelect.value); syncCategoryId(); });
    childSelect.addEventListener('change', syncCategoryId);

    if (!parentSelect.value && selectedCategoryId) {
        let matchedChild = false;
        Object.entries(childMap).forEach(([parentId, children]) => {
            if (!matchedChild && children.some(item => Number(item.id) === Number(selectedCategoryId))) {
                parentSelect.value = parentId;
                matchedChild = true;
            }
        });
        if (!matchedChild && childMap[String(selectedCategoryId)] !== undefined) {
            parentSelect.value = String(selectedCategoryId);
        }
    }

    renderChildOptions(parentSelect.value);
    syncCategoryId();

    function createGalleryInput(name, value = '') {
        const wrapper = document.createElement('div');
        wrapper.className = 'input-group mb-2';
        wrapper.innerHTML = `
            <input type="url" name="${name}[]" class="form-control" placeholder="https://..." value="${value}">
            <button type="button" class="btn btn-outline-secondary open-gallery-media"><i class="fa fa-image"></i></button>
            <button type="button" class="btn btn-outline-danger remove-gallery-item"><i class="fa fa-times"></i></button>
        `;
        wrapper.querySelector('.remove-gallery-item').addEventListener('click', () => wrapper.remove());
        wrapper.querySelector('.open-gallery-media').addEventListener('click', () => {
            const input = wrapper.querySelector('input');
            openMediaManager({ mode: 'gallery', onSelect: (item) => input.value = item.url });
        });
        return wrapper;
    }

    const photoList = document.getElementById('photo-gallery-list');
    const videoList = document.getElementById('video-gallery-list');
    const photoValues = @json(old('photo_gallery', $post->photo_gallery ?? []));
    const videoValues = @json(old('video_gallery', $post->video_gallery ?? []));

    document.getElementById('add-photo-gallery-item').addEventListener('click', () => photoList.appendChild(createGalleryInput('photo_gallery')));
    document.getElementById('add-video-gallery-item').addEventListener('click', () => videoList.appendChild(createGalleryInput('video_gallery')));

    (photoValues.length ? photoValues : ['']).forEach(item => photoList.appendChild(createGalleryInput('photo_gallery', item)));
    (videoValues.length ? videoValues : ['']).forEach(item => videoList.appendChild(createGalleryInput('video_gallery', item)));

    window.addEventListener('DOMContentLoaded', function () {
        initCkEditorWithMedia('#editor');

        const coverPathInput = document.getElementById('cover_image_path');
        const coverPreview = document.getElementById('cover_image_preview');
        const coverWrap = document.getElementById('cover_image_preview_wrap');

        document.getElementById('open_cover_media_picker').addEventListener('click', function () {
            openMediaManager({
                mode: 'cover',
                onSelect: function (item) {
                    coverPathInput.value = item.path;
                    coverPreview.src = item.url;
                    coverWrap.classList.remove('d-none');
                }
            });
        });

        document.getElementById('clear_cover_media_picker').addEventListener('click', function () {
            coverPathInput.value = '';
            coverPreview.src = '';
            coverWrap.classList.add('d-none');
        });
    });
</script>
@endsection
