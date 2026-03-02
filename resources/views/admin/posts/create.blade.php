@extends('layouts.admin')

@section('content')
@php
    $selectedCategoryId = (int) old('category_id', 0);
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

    <form action="{{ route('admin.posts.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="category_id" id="category_id" value="{{ $selectedCategoryId }}">
        <input type="hidden" name="image_path" id="cover_image_path" value="{{ old('image_path') }}">

        <div class="row">
            <div class="col-md-9">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="fw-bold mb-2">Baslik</label>
                            <input type="text" name="title" class="form-control form-control-lg" value="{{ old('title') }}" placeholder="Haber basligini girin..." required>
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
                                <select name="type" class="form-select">
                                    <option value="normal" @selected(old('type') === 'normal')>Normal</option>
                                    <option value="manset" @selected(old('type') === 'manset')>Manset</option>
                                    <option value="surmanset" @selected(old('type') === 'surmanset')>Surmanset</option>
                                    <option value="top_manset" @selected(old('type') === 'top_manset')>Top Manset</option>
                                    <option value="spor_manset" @selected(old('type') === 'spor_manset')>Spor Manset</option>
                                    <option value="ekonomi_manset" @selected(old('type') === 'ekonomi_manset')>Ekonomi Manset</option>
                                    <option value="gizli" @selected(old('type') === 'gizli')>Gizli</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="fw-bold mb-2 small">Yayin Tarihi</label>
                                <input type="datetime-local" name="published_at" class="form-control" value="{{ old('published_at', date('Y-m-d\\TH:i')) }}">
                            </div>
                            <div class="col-md-2">
                                <label class="fw-bold mb-2 small">Sehir</label>
                                <input type="text" name="city" class="form-control" value="{{ old('city') }}" placeholder="Opsiyonel">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="fw-bold mb-2">Ozet</label>
                            <textarea name="summary" class="form-control" rows="3" placeholder="Haberin kisa ozeti...">{{ old('summary') }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label class="fw-bold mb-2">Etiketler</label>
                            <input type="text" name="tags" class="form-control" value="{{ old('tags') }}" placeholder="Virgul ile ayirin (or: spor,futbol,turkiye)">
                        </div>

                        @include('admin.components.rich-editor-field', [
                            'label' => 'Icerik',
                            'name' => 'content',
                            'id' => 'editor',
                            'value' => old('content'),
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
                    <div class="card-body">
                        <div id="cover_image_preview_wrap" class="mb-2 {{ old('image_path') ? '' : 'd-none' }}">
                            <img id="cover_image_preview" src="{{ old('image_path') ? asset('storage/' . ltrim(old('image_path'), '/')) : '' }}" class="img-fluid rounded border" alt="Kapak Resmi">
                        </div>
                        <div class="d-grid gap-2 mb-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="open_cover_media_picker">
                                <i class="fa fa-images me-1"></i> Kutuphaneden Sec
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="clear_cover_media_picker">
                                <i class="fa fa-times me-1"></i> Secimi Temizle
                            </button>
                        </div>
                        <small class="text-muted d-block">Kapak resmi sadece medya kutuphanesinden secilir. Onerilen boyut: 800x600px</small>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white fw-bold small text-secondary">YAYIN OZELLIKLERI</div>
                    <div class="card-body py-2">
                        <div class="form-check form-switch my-2">
                            <input class="form-check-input" type="checkbox" name="is_breaking" id="is_breaking" value="1" @checked(old('is_breaking'))>
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
                        <div class="small text-muted mt-2">Her satir bir gorsel URL'si.</div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white fw-bold small text-secondary">VIDEO GALERI</div>
                    <div class="card-body">
                        <div id="video-gallery-list"></div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-video-gallery-item">
                            <i class="fa fa-plus me-1"></i> Video URL Ekle
                        </button>
                        <div class="small text-muted mt-2">YouTube/Vimeo linkleri onerilir.</div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <button type="submit" class="btn btn-success w-100 py-3 fw-bold">
                            <i class="fa fa-paper-plane me-2"></i> Yayinla
                        </button>
                    </div>
                </div>
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
            if (Number(child.id) === Number(preferredChildId)) {
                option.selected = true;
            }
            childSelect.appendChild(option);
        }
        childSelect.disabled = children.length === 0;
    }

    function syncCategoryId() {
        const parentId = parentSelect.value;
        const childId = childSelect.value;
        categoryInput.value = childId || parentId || '';
    }

    parentSelect.addEventListener('change', () => {
        renderChildOptions(parentSelect.value);
        syncCategoryId();
    });
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
            <button type="button" class="btn btn-outline-secondary open-gallery-media" data-target-name="${name}"><i class="fa fa-image"></i></button>
            <button type="button" class="btn btn-outline-danger remove-gallery-item"><i class="fa fa-times"></i></button>
        `;
        wrapper.querySelector('.remove-gallery-item').addEventListener('click', () => wrapper.remove());
        wrapper.querySelector('.open-gallery-media').addEventListener('click', () => {
            const input = wrapper.querySelector('input');
            openMediaManager({
                mode: 'gallery',
                onSelect: function (item) { input.value = item.url; }
            });
        });
        return wrapper;
    }

    const photoList = document.getElementById('photo-gallery-list');
    const videoList = document.getElementById('video-gallery-list');
    const oldPhotoValues = @json(old('photo_gallery', []));
    const oldVideoValues = @json(old('video_gallery', []));

    document.getElementById('add-photo-gallery-item').addEventListener('click', () => photoList.appendChild(createGalleryInput('photo_gallery')));
    document.getElementById('add-video-gallery-item').addEventListener('click', () => videoList.appendChild(createGalleryInput('video_gallery')));

    (oldPhotoValues.length ? oldPhotoValues : ['']).forEach(item => photoList.appendChild(createGalleryInput('photo_gallery', item)));
    (oldVideoValues.length ? oldVideoValues : ['']).forEach(item => videoList.appendChild(createGalleryInput('video_gallery', item)));

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
