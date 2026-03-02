<script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
<script>
(function () {
    if (window.__mediaManagerInitialized) return;
    window.__mediaManagerInitialized = true;

    const routes = {
        list: @json(route('admin.media.index')),
        upload: @json(route('admin.media.upload')),
        freeSearch: @json(route('admin.media.free-search')),
        importRemote: @json(route('admin.media.import-remote')),
        favoriteBase: @json(url('/admin/medya-kutuphanesi')),
    };
    const csrf = @json(csrf_token());
    const mediaButtonIcon = @json(asset('images/admin/media-library.svg'));

    const state = {
        mode: 'cover',
        onSelect: null,
        currentTab: 'library',
        library: { page: 1, q: '', loading: false, hasMore: true, items: [] },
        favorites: { page: 1, q: '', loading: false, hasMore: true, items: [] },
    };

    let modalEl = null;
    let modalInstance = null;

    function ensureModal() {
        modalEl = document.getElementById('mediaLibraryModal');
        if (!modalEl) return null;
        if (!modalInstance) modalInstance = new bootstrap.Modal(modalEl);
        bindModalEvents();
        return modalInstance;
    }

    let eventsBound = false;
    function bindModalEvents() {
        if (eventsBound || !modalEl) return;
        eventsBound = true;

        modalEl.querySelectorAll('[data-media-tab]').forEach(btn => {
            btn.addEventListener('click', () => switchTab(btn.getAttribute('data-media-tab')));
        });
        document.getElementById('mediaLibrarySearchBtn')?.addEventListener('click', () => reloadCurrentLibrary());
        document.getElementById('mediaLibrarySearchInput')?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') { e.preventDefault(); reloadCurrentLibrary(); }
        });
        document.getElementById('mediaLibraryLoadMoreBtn')?.addEventListener('click', loadMoreCurrentLibrary);
        document.getElementById('mediaUploadForm')?.addEventListener('submit', uploadAsset);
        document.getElementById('freeImageSearchBtn')?.addEventListener('click', searchFreeImages);
        document.getElementById('freeImageSearchInput')?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') { e.preventDefault(); searchFreeImages(); }
        });
    }

    function switchTab(tab) {
        state.currentTab = tab;
        modalEl.querySelectorAll('[data-media-tab]').forEach(btn => {
            btn.classList.toggle('active', btn.getAttribute('data-media-tab') === tab);
        });
        ['library', 'upload', 'free', 'favorites'].forEach(key => {
            const el = document.getElementById('mediaTab' + key.charAt(0).toUpperCase() + key.slice(1));
            if (!el) return;
            el.classList.toggle('d-none', key !== tab);
        });
        if (tab === 'library' || tab === 'favorites') reloadCurrentLibrary();
    }

    function currentLibState() {
        return state.currentTab === 'favorites' ? state.favorites : state.library;
    }

    function reloadCurrentLibrary() {
        const s = currentLibState();
        s.page = 1;
        s.items = [];
        s.hasMore = true;
        s.q = (document.getElementById('mediaLibrarySearchInput')?.value || '').trim();
        loadMoreCurrentLibrary(true);
    }

    async function loadMoreCurrentLibrary(reset) {
        const s = currentLibState();
        if (s.loading || !s.hasMore) return;
        s.loading = true;
        const grid = document.getElementById('mediaLibraryGrid');
        if (reset) grid.innerHTML = '';

        try {
            const url = new URL(routes.list, window.location.origin);
            url.searchParams.set('tab', state.currentTab);
            url.searchParams.set('page', s.page);
            if (s.q) url.searchParams.set('q', s.q);

            const res = await fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();
            if (!data.ok) throw new Error('Liste alinamadi');

            s.items = s.items.concat(data.items || []);
            renderLibraryItems(data.items || [], reset);
            const meta = data.meta || {};
            s.hasMore = (meta.current_page || 1) < (meta.last_page || 1);
            s.page = (meta.current_page || 1) + 1;

            document.getElementById('mediaLibraryMeta').textContent = `Toplam: ${meta.total ?? s.items.length}`;
            document.getElementById('mediaLibraryLoadMoreBtn').classList.toggle('d-none', !s.hasMore);
        } catch (e) {
            grid.innerHTML = '<div class="col-12"><div class="alert alert-danger mb-0">Medya listesi alinamadi.</div></div>';
        } finally {
            s.loading = false;
        }
    }

    function renderLibraryItems(items, reset = false) {
        const grid = document.getElementById('mediaLibraryGrid');
        if (reset) grid.innerHTML = '';
        if ((!items || !items.length) && reset) {
            grid.innerHTML = '<div class="col-12"><div class="alert alert-light border mb-0">Kayit bulunamadi.</div></div>';
            return;
        }
        items.forEach(item => {
            const col = document.createElement('div');
            col.className = 'col-md-3 col-6';
            col.innerHTML = `
                <div class="card h-100 shadow-sm">
                    <div style="height:140px;background:#f8fafc;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                        <img src="${escapeHtml(item.url)}" alt="${escapeHtml(item.title || item.original_name || 'resim')}" style="max-width:100%;max-height:100%;object-fit:cover;">
                    </div>
                    <div class="card-body p-2">
                        <div class="small fw-semibold text-truncate">${escapeHtml(item.title || item.original_name || 'Resim')}</div>
                        <div class="small text-muted">${item.width || '-'}x${item.height || '-'}</div>
                        <div class="d-flex gap-1 mt-2">
                            <button type="button" class="btn btn-sm btn-primary flex-grow-1 js-media-select">Sec</button>
                            <button type="button" class="btn btn-sm ${item.is_favorite ? 'btn-warning' : 'btn-outline-warning'} js-media-fav" title="Yildiz">
                                <i class="fa fa-star"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            col.querySelector('.js-media-select').addEventListener('click', () => selectMedia(item));
            col.querySelector('.js-media-fav').addEventListener('click', () => toggleFavorite(item.id));
            grid.appendChild(col);
        });
    }

    async function toggleFavorite(id) {
        try {
            await fetch(`${routes.favoriteBase}/${id}/favori`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });
            reloadCurrentLibrary();
        } catch (e) {}
    }

    async function uploadAsset(e) {
        e.preventDefault();
        const form = e.currentTarget;
        const fd = new FormData(form);
        const box = document.getElementById('mediaUploadResult');
        box.innerHTML = '<div class="alert alert-info py-2">Yukleniyor...</div>';
        try {
            const res = await fetch(routes.upload, {
                method: 'POST',
                body: fd,
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });
            const data = await res.json();
            if (!res.ok || !data.ok) throw new Error(data.message || 'Yukleme hatasi');
            box.innerHTML = '<div class="alert alert-success py-2 mb-2">Resim kutuphaneye eklendi.</div>';
            form.reset();
            switchTab('library');
        } catch (err) {
            box.innerHTML = `<div class="alert alert-danger py-2 mb-0">${escapeHtml(err.message || 'Yukleme basarisiz')}</div>`;
        }
    }

    async function searchFreeImages() {
        const q = (document.getElementById('freeImageSearchInput')?.value || '').trim();
        const grid = document.getElementById('freeImageGrid');
        if (!q) return;
        grid.innerHTML = '<div class="col-12"><div class="alert alert-info mb-0">Araniyor...</div></div>';
        try {
            const url = new URL(routes.freeSearch, window.location.origin);
            url.searchParams.set('q', q);
            const res = await fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();
            if (!res.ok || !data.ok) throw new Error(data.message || 'Arama basarisiz');
            renderFreeResults(data.items || []);
        } catch (e) {
            grid.innerHTML = '<div class="col-12"><div class="alert alert-danger mb-0">Ucretsiz resim aramasi basarisiz.</div></div>';
        }
    }

    function renderFreeResults(items) {
        const grid = document.getElementById('freeImageGrid');
        grid.innerHTML = '';
        if (!items.length) {
            grid.innerHTML = '<div class="col-12"><div class="alert alert-light border mb-0">Sonuc bulunamadi.</div></div>';
            return;
        }
        items.forEach(item => {
            const col = document.createElement('div');
            col.className = 'col-md-3 col-6';
            col.innerHTML = `
                <div class="card h-100 shadow-sm">
                    <img src="${escapeHtml(item.thumb_url)}" alt="${escapeHtml(item.title || 'img')}" style="height:140px;object-fit:cover;">
                    <div class="card-body p-2">
                        <div class="small fw-semibold">${escapeHtml((item.title || '').replace(/^File:/i,''))}</div>
                        <div class="small text-muted">${escapeHtml(item.license || '')}</div>
                        <div class="small text-muted text-truncate">${escapeHtml(item.credit || '')}</div>
                        <button type="button" class="btn btn-sm btn-success w-100 mt-2 js-import-free">Kutuphane'ye Ekle</button>
                    </div>
                </div>
            `;
            col.querySelector('.js-import-free').addEventListener('click', () => importFreeImage(item));
            grid.appendChild(col);
        });
    }

    async function importFreeImage(item) {
        try {
            const res = await fetch(routes.importRemote, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    source_url: item.source_url,
                    title: (item.title || '').replace(/^File:/i, ''),
                    credit: item.credit || '',
                    provider: item.provider || 'wikimedia'
                })
            });
            const data = await res.json();
            if (!res.ok || !data.ok) throw new Error(data.message || 'Ice aktarma basarisiz');
            selectMedia(data.asset);
        } catch (e) {
            alert(e.message || 'Resim ice aktarilamadi.');
        }
    }

    function selectMedia(item) {
        if (typeof state.onSelect === 'function') {
            state.onSelect(item);
        }
        modalInstance?.hide();
    }

    function openMediaManager(options = {}) {
        state.mode = options.mode || 'cover';
        state.onSelect = options.onSelect || null;
        const modal = ensureModal();
        if (!modal) return;
        const initialTab = options.tab || 'library';
        switchTab(initialTab);
        modal.show();
    }

    function initCkEditorWithMedia(selector) {
        const el = document.querySelector(selector);
        if (!el || typeof CKEDITOR === 'undefined') return;

        const elementId = el.id || ('editor_' + Math.random().toString(36).slice(2));
        if (!el.id) el.id = elementId;

        if (CKEDITOR.instances[elementId]) {
            CKEDITOR.instances[elementId].destroy(true);
        }

        if (!window.__ckEditorMediaCssApplied) {
            window.__ckEditorMediaCssApplied = true;
            CKEDITOR.addCss("body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen,Ubuntu,Cantarell,'Open Sans','Helvetica Neue',sans-serif;}");
        }

        CKEDITOR.replace(elementId, {
            height: 520,
            allowedContent: true,
            versionCheck: false,
            removePlugins: 'elementspath',
            toolbar: [
                { name: 'clipboard', items: ['Undo', 'Redo'] },
                { name: 'styles', items: ['Format'] },
                { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'RemoveFormat'] },
                { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Blockquote', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight'] },
                { name: 'insert', items: ['Link', 'Unlink', 'Table', 'HorizontalRule', 'MediaLibrary'] },
                { name: 'tools', items: ['Maximize', 'Source'] }
            ],
            contentsLangDirection: 'ltr'
        });

        const editor = CKEDITOR.instances[elementId];
        editor.addCommand('openMediaLibrary', {
            exec: function (ed) {
                openMediaManager({
                    mode: 'editor',
                    onSelect: function (item) {
                        const alt = String(item.alt_text || item.title || '').replace(/"/g, '&quot;');
                        ed.insertHtml(`<p><img src="${item.url}" alt="${alt}" style="max-width:100%;height:auto;"></p>`);
                    }
                });
            }
        });
        editor.ui.addButton('MediaLibrary', {
            label: 'Medya Kutuphanesi',
            command: 'openMediaLibrary',
            toolbar: 'insert,50',
            icon: mediaButtonIcon
        });
    }

    function escapeHtml(v) {
        return String(v ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    window.openMediaManager = openMediaManager;
    window.initCkEditorWithMedia = initCkEditorWithMedia;
})();
</script>
