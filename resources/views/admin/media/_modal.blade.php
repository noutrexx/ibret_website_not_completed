<div class="modal fade" id="mediaLibraryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Medya Yonetici</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-3" id="mediaTabs">
                    <li class="nav-item"><button type="button" class="nav-link active" data-media-tab="library">Kutuphane</button></li>
                    <li class="nav-item"><button type="button" class="nav-link" data-media-tab="favorites">Yildizli</button></li>
                    <li class="nav-item"><button type="button" class="nav-link" data-media-tab="upload">Yukle</button></li>
                    <li class="nav-item"><button type="button" class="nav-link" data-media-tab="free">Ucretsiz Resim Bul</button></li>
                </ul>

                <div id="mediaTabLibrary" class="media-tab-pane">
                    <div class="d-flex gap-2 mb-3">
                        <input type="text" class="form-control" id="mediaLibrarySearchInput" placeholder="Resim ara...">
                        <button type="button" class="btn btn-outline-secondary" id="mediaLibrarySearchBtn">Ara</button>
                    </div>
                    <div id="mediaLibraryGrid" class="row g-3"></div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <small class="text-muted" id="mediaLibraryMeta">-</small>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="mediaLibraryLoadMoreBtn">Daha Fazla</button>
                    </div>
                </div>

                <div id="mediaTabUpload" class="media-tab-pane d-none">
                    <form id="mediaUploadForm" class="border rounded p-3 bg-light">
                        <label class="form-label fw-semibold">Resim Yukle</label>
                        <input type="file" name="file" accept="image/*" class="form-control mb-2" required>
                        <button type="submit" class="btn btn-primary">Yukle</button>
                        <div class="small text-muted mt-2">jpg/png/webp/gif - max 5MB</div>
                    </form>
                    <div id="mediaUploadResult" class="mt-3"></div>
                </div>

                <div id="mediaTabFree" class="media-tab-pane d-none">
                    <div class="alert alert-warning py-2 small">
                        Wikimedia Commons uzerinden telifsiz/ozgur lisansli gorseller aranir. Kullanmadan once lisans/kredi bilgisini kontrol edin.
                    </div>
                    <div class="d-flex gap-2 mb-3">
                        <input type="text" class="form-control" id="freeImageSearchInput" placeholder="Orn: football stadium, economy, gold">
                        <button type="button" class="btn btn-outline-secondary" id="freeImageSearchBtn">Ara</button>
                    </div>
                    <div id="freeImageGrid" class="row g-3"></div>
                </div>
            </div>
            <div class="modal-footer">
                <small class="text-muted me-auto" id="mediaSelectionHint">Bir resim secin.</small>
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

