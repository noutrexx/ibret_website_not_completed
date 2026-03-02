@extends('frontend.layouts.app')

@section('content')
    <div style="width:1060px;max-width:100%;margin:0 auto;">
        <section style="margin-top:20px;">
            <div class="line line-title" style="height:40px;margin-bottom:12px;">
                <div class="line-img" style="width:24px;height:24px;display:flex;align-items:center;justify-content:center;margin-right:8px;">
                    <img alt="GAZETELER" width="20" height="20" src="https://img.icons8.com/color/48/newspaper.png" style="width:20px;height:20px;display:block;border-radius:2px;">
                </div>
                <div class="line__container" style="background-color:#f5e629;">
                    <h2 class="line__title">GAZETELER</h2>
                    <span class="line__link" style="cursor:default;">
                        <span class="line--text" style="background-color:#f5e629;">BUGUN</span>
                    </span>
                </div>
            </div>

            @if($newspaperError)
                <div style="margin-bottom:14px;padding:12px 14px;border-radius:12px;border:1px solid #fecaca;background:#fff5f5;color:#991b1b;font-size:13px;">
                    {{ $newspaperError }}
                </div>
            @endif

            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                <div style="font-size:13px;color:#475569;">
                    Kaynak: <strong>{{ strtoupper($newspaperSource ?? 'gazeteoku') }}</strong>
                </div>
                <div style="font-size:13px;color:#475569;">
                    Toplam: <strong>{{ $newspaperItems->count() }}</strong> gazete
                </div>
            </div>

            <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:14px;">
                @forelse($newspaperItems as $item)
                    <button type="button"
                       class="newspaper-open-btn"
                       data-image="{{ $item['image_url'] }}"
                       data-title="{{ e($item['name']) }}"
                       style="text-decoration:none;display:flex;flex-direction:column;border-radius:12px;overflow:hidden;background:#fff;border:1px solid #eef2f7;box-shadow:0 8px 20px rgba(15,23,42,.05), 0 1px 2px rgba(15,23,42,.04);transition:transform .18s ease, box-shadow .18s ease;"
                       onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 14px 26px rgba(15,23,42,.08),0 3px 8px rgba(15,23,42,.05)'"
                       onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 8px 20px rgba(15,23,42,.05), 0 1px 2px rgba(15,23,42,.04)'">
                        <div style="height:270px;background:#f8fafc;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                            <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}" loading="lazy"
                                 style="width:100%;height:270px;object-fit:cover;display:block;">
                        </div>
                        <div style="padding:10px 10px 12px;">
                            <div style="font-size:13px;font-weight:800;line-height:1.3;color:#111827;min-height:34px;">
                                {{ $item['name'] }}
                            </div>
                            @if(!empty($item['date_text']))
                                <div style="margin-top:6px;font-size:11px;color:#64748b;">
                                    {{ $item['date_text'] }}
                                </div>
                            @endif
                        </div>
                    </button>
                @empty
                    <div style="grid-column:1/-1;border-radius:12px;background:#fff;border:1px solid #eef2f7;padding:18px;color:#64748b;">
                        Gazete verisi bulunamadi.
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    <div id="newspaper-modal"
         style="display:none;position:fixed;inset:0;z-index:1200;background:rgba(2,6,23,.72);backdrop-filter:blur(2px);padding:24px;align-items:center;justify-content:center;">
        <div id="newspaper-modal-panel"
             style="position:relative;width:min(980px,100%);max-height:92vh;background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 22px 60px rgba(2,6,23,.35);display:flex;flex-direction:column;">
            <div style="height:52px;display:flex;align-items:center;justify-content:space-between;padding:0 14px;border-bottom:1px solid #eef2f7;background:#fff;">
                <div id="newspaper-modal-title" style="font-size:14px;font-weight:700;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;padding-right:10px;">
                    Gazete
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <button type="button" id="newspaper-modal-close"
                            style="width:34px;height:34px;border:1px solid #e5e7eb;border-radius:10px;background:#fff;color:#111827;font-size:18px;line-height:1;">
                        ×
                    </button>
                </div>
            </div>
            <div style="padding:12px;background:#f8fafc;overflow:auto;max-height:calc(92vh - 52px);">
                <img id="newspaper-modal-image" src="" alt="" style="width:100%;height:auto;display:block;border-radius:10px;background:#fff;">
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .line-title {
        display: flex;
        align-items: center;
        width: 100%;
    }
    .line__container {
        height: 40px;
        width: calc(100% - 32px);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 12px;
        border-radius: 8px;
    }
    .line__title {
        margin: 0;
        font-size: 16px;
        font-weight: 800;
        letter-spacing: .02em;
        color: #111827;
    }
    .line__link {
        text-decoration: none;
        color: #111827;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
    }
    .line--text {
        padding: 2px 6px;
        border-radius: 4px;
    }
    @media (max-width: 1100px) {
        .newspapers-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        const modal = document.getElementById('newspaper-modal');
        const modalImage = document.getElementById('newspaper-modal-image');
        const modalTitle = document.getElementById('newspaper-modal-title');
        const closeBtn = document.getElementById('newspaper-modal-close');
        if (!modal || !modalImage || !modalTitle || !closeBtn) return;

        function openModal(imageUrl, title) {
            modalImage.src = imageUrl || '';
            modalImage.alt = title || 'Gazete';
            modalTitle.textContent = title || 'Gazete';
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            modal.style.display = 'none';
            document.body.style.overflow = '';
            modalImage.src = '';
        }

        document.querySelectorAll('.newspaper-open-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                openModal(btn.dataset.image, btn.dataset.title);
            });
        });

        closeBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.style.display === 'flex') {
                closeModal();
            }
        });
    })();
</script>
@endpush
