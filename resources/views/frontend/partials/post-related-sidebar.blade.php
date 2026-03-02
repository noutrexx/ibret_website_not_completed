@if(($related ?? collect())->isNotEmpty())
    <div style="display:flex;flex-direction:column;gap:10px;">
        @foreach($related as $rel)
            @php $relTags = $rel->tagList(); @endphp
            <a href="{{ $rel->frontend_url }}" style="display:block;text-decoration:none;color:#111;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;background:#fff;">
                <div style="width:100%;height:130px;background:#f3f4f6;overflow:hidden;">
                    @if($rel->image)
                        <img src="{{ asset('storage/' . $rel->image) }}" alt="{{ $rel->title }}" style="width:100%;height:130px;object-fit:cover;display:block;">
                    @endif
                </div>
                <div style="padding:10px;">
                    <div style="font-size:13px;line-height:1.35;font-weight:700;">{{ \Illuminate\Support\Str::limit($rel->title, 70) }}</div>
                    <div style="margin-top:8px;font-size:11px;color:#6b7280;">
                        @if(!empty($relTags))
                            #{{ $relTags[0] }}
                        @else
                            #haber
                        @endif
                    </div>
                </div>
            </a>
        @endforeach
    </div>
@endif
