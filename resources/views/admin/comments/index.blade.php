@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success shadow-sm border-0">{{ session('success') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Yorumlar</h3>
    </div>

    <div class="mb-3 d-flex gap-2">
        <a href="{{ route('admin.comments.index', ['status' => 'pending']) }}" class="btn btn-sm {{ $status === 'pending' ? 'btn-primary' : 'btn-outline-primary' }}">Bekleyen</a>
        <a href="{{ route('admin.comments.index', ['status' => 'approved']) }}" class="btn btn-sm {{ $status === 'approved' ? 'btn-success' : 'btn-outline-success' }}">Onayli</a>
        <a href="{{ route('admin.comments.index', ['status' => 'rejected']) }}" class="btn btn-sm {{ $status === 'rejected' ? 'btn-danger' : 'btn-outline-danger' }}">Reddedilen</a>
        <a href="{{ route('admin.comments.index', ['status' => 'all']) }}" class="btn btn-sm {{ $status === 'all' ? 'btn-dark' : 'btn-outline-dark' }}">Tum Yorumlar</a>
    </div>

    <div class="card shadow border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3">ID</th>
                            <th>Haber</th>
                            <th>Ad</th>
                            <th>Yorum</th>
                            <th>Durum</th>
                            <th>Tarih</th>
                            <th class="text-end pe-3">Islem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($comments as $comment)
                            <tr>
                                <td class="ps-3">{{ $comment->id }}</td>
                                <td style="max-width:220px;">
                                    @if($comment->post)
                                        <a href="{{ $comment->post->frontend_url }}" target="_blank" class="text-decoration-none">
                                            {{ \Illuminate\Support\Str::limit($comment->post->title, 70) }}
                                        </a>
                                    @else
                                        <span class="text-muted">Silinmis haber</span>
                                    @endif
                                </td>
                                <td>{{ $comment->name }}</td>
                                <td style="max-width:340px;">{{ \Illuminate\Support\Str::limit($comment->content, 140) }}</td>
                                <td>
                                    @if($comment->status === 'approved')
                                        <span class="badge bg-success">Onayli</span>
                                    @elseif($comment->status === 'rejected')
                                        <span class="badge bg-danger">Reddedildi</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Bekliyor</span>
                                    @endif
                                </td>
                                <td>{{ $comment->created_at?->format('d.m.Y H:i') }}</td>
                                <td class="text-end pe-3">
                                    @if($comment->status !== 'approved')
                                        <form action="{{ route('admin.comments.approve', $comment->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success">Onayla</button>
                                        </form>
                                    @endif
                                    @if($comment->status !== 'rejected')
                                        <form action="{{ route('admin.comments.reject', $comment->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-warning">Reddet</button>
                                        </form>
                                    @endif
                                    <form action="{{ route('admin.comments.destroy', $comment->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yorum silinsin mi?')">Sil</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Yorum bulunamadi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $comments->links() }}
    </div>
</div>
@endsection

