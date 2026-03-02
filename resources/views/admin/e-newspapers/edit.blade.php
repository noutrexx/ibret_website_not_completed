@extends('layouts.admin')

@section('content')
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

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div class="fw-bold">E-Gazete Haber Yerlesimi</div>
            <a href="{{ route('admin.e-newspapers.index') }}" class="btn btn-sm btn-outline-secondary">Geri Don</a>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.e-newspapers.update', $issue->id) }}">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Baslik</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $issue->title) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Sayi Tarihi</label>
                        <input type="date" name="issue_date" class="form-control" value="{{ old('issue_date', optional($issue->issue_date)->toDateString()) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Ozet</label>
                        <input type="text" name="summary" class="form-control" value="{{ old('summary', $issue->summary) }}">
                    </div>
                </div>

                @foreach($slotGroups as $group => $slots)
                    <div class="mb-4">
                        <div class="fw-bold text-uppercase mb-2">{{ $group }}</div>
                        <div class="row g-2">
                            @foreach($slots as $slot)
                                @php
                                    $field = 'slots.' . $slot['key'];
                                    $inputName = 'slots[' . $slot['key'] . ']';
                                @endphp
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">{{ $slot['label'] }}</label>
                                    <select name="{{ $inputName }}" class="form-select form-select-sm">
                                        <option value="">Secilmedi</option>
                                        @foreach($postOptions as $post)
                                            @php
                                                $label = ($post->category?->name ? '[' . $post->category->name . '] ' : '') . $post->title;
                                                $published = optional($post->published_at)->format('d.m H:i');
                                            @endphp
                                            <option value="{{ $post->id }}" @selected((int) old($field, $slotValues[$slot['key']] ?? 0) === (int) $post->id)>
                                                {{ \Illuminate\Support\Str::limit($label, 90) }}{{ $published ? ' - ' . $published : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($group === 'SOL KOLON')
                                        <div class="form-check mt-1">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   name="image_controls[{{ $slot['key'] }}]"
                                                   value="1"
                                                   id="img_ctrl_{{ $slot['key'] }}"
                                                   @checked((bool) old('image_controls.' . $slot['key'], $slotImageControls[$slot['key']] ?? true))>
                                            <label class="form-check-label small text-muted" for="img_ctrl_{{ $slot['key'] }}">
                                                Gorsel goster
                                            </label>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i> Yerlesimi Kaydet
                    </button>
                    <a href="{{ route('enewspapers.print', $issue->slug) }}{{ $issue->status !== 'published' ? '?preview=1' : '' }}" target="_blank" class="btn btn-outline-dark">
                        <i class="fa fa-file-lines me-1"></i> Gazete Gorunumu
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
