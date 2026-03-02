@extends('frontend.layouts.app')

@section('content')
<div style="width:1060px;max-width:100%;margin:0 auto;">
    <section style="margin-top:20px;">
        <div class="line line-title" style="height:40px;margin-bottom:12px;">
            <div class="line-img" style="width:24px;height:24px;display:flex;align-items:center;justify-content:center;margin-right:8px;">
                <img alt="E-GAZETE" width="20" height="20" src="https://img.icons8.com/color/48/newspaper.png" style="width:20px;height:20px;display:block;border-radius:2px;">
            </div>
            <div class="line__container" style="background-color:#f5e629;">
                <h2 class="line__title">E-GAZETE ARSIVI</h2>
                <span class="line__link"><span class="line--text" style="background-color:#f5e629;">{{ $issues->total() }} SAYI</span></span>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;">
            @forelse($issues as $issue)
                @php $cover = optional($issue->items->firstWhere('section', 'manset') ?? null)?->image; @endphp
                <a href="{{ route('enewspapers.show', $issue->slug) }}"
                   style="text-decoration:none;display:flex;flex-direction:column;border-radius:12px;overflow:hidden;background:#fff;border:1px solid #eef2f7;box-shadow:0 8px 20px rgba(15,23,42,.05),0 1px 2px rgba(15,23,42,.04);transition:transform .18s ease, box-shadow .18s ease;"
                   onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 14px 26px rgba(15,23,42,.08),0 3px 8px rgba(15,23,42,.05)'"
                   onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 8px 20px rgba(15,23,42,.05),0 1px 2px rgba(15,23,42,.04)'">
                    <div style="height:220px;background:#f8fafc;overflow:hidden;">
                        @if($cover)
                            <img src="{{ asset('storage/' . $cover) }}" alt="{{ $issue->title }}" style="width:100%;height:220px;object-fit:cover;display:block;">
                        @else
                            <div style="width:100%;height:220px;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-weight:700;">E-GAZETE</div>
                        @endif
                    </div>
                    <div style="padding:12px;">
                        <div style="font-size:14px;font-weight:800;color:#111827;line-height:1.3;">{{ $issue->title }}</div>
                        <div style="margin-top:6px;font-size:12px;color:#64748b;">
                            {{ optional($issue->issue_date)->format('d.m.Y') }} · {{ $issue->items_count }} haber
                        </div>
                    </div>
                </a>
            @empty
                <div style="grid-column:1/-1;border-radius:12px;background:#fff;border:1px solid #eef2f7;padding:18px;color:#64748b;">
                    Henuz yayinlanmis e-gazete yok.
                </div>
            @endforelse
        </div>

        <div style="margin-top:16px;">
            {{ $issues->links() }}
        </div>
    </section>
</div>
@endsection

@push('styles')
<style>
    .line-title { display:flex; align-items:center; width:100%; }
    .line__container { height:40px; width:calc(100% - 32px); display:flex; align-items:center; justify-content:space-between; padding:0 12px; border-radius:8px; }
    .line__title { margin:0; font-size:16px; font-weight:800; letter-spacing:.02em; color:#111827; }
    .line__link { text-decoration:none; color:#111827; font-size:12px; font-weight:700; text-transform:uppercase; }
    .line--text { padding:2px 6px; border-radius:4px; }
</style>
@endpush

