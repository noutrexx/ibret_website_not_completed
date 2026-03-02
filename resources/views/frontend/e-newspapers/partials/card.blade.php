<a href="{{ $item->post_url }}"
   style="text-decoration:none;display:flex;flex-direction:column;border-radius:12px;overflow:hidden;background:#fff;border:1px solid #eef2f7;box-shadow:0 8px 18px rgba(15,23,42,.05),0 1px 2px rgba(15,23,42,.04);transition:transform .18s ease, box-shadow .18s ease;"
   onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 14px 24px rgba(15,23,42,.07),0 3px 6px rgba(15,23,42,.04)'"
   onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 8px 18px rgba(15,23,42,.05),0 1px 2px rgba(15,23,42,.04)'">
    <div style="height:{{ (int) $h }}px;background:#f1f5f9;overflow:hidden;">
        @if($img)
            <img src="{{ $img }}" alt="{{ $item->title }}" style="width:100%;height:{{ (int) $h }}px;object-fit:cover;display:block;">
        @endif
    </div>
    <div style="padding:12px;">
        <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:700;letter-spacing:.04em;">
            {{ $item->category_name ?: 'Genel' }}
        </div>
        <div style="margin-top:6px;font-size:14px;font-weight:800;line-height:1.3;color:#111827;">
            {{ \Illuminate\Support\Str::limit($item->title, 95) }}
        </div>
        @if($item->summary)
            <div style="margin-top:7px;font-size:12px;color:#64748b;line-height:1.4;">
                {{ \Illuminate\Support\Str::limit($item->summary, 90) }}
            </div>
        @endif
    </div>
</a>

