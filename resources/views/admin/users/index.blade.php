@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-0"><i class="fa fa-users me-2 text-primary"></i> Ekip Yönetimi</h3>
            <p class="text-muted small mb-0">Yöneticiler, Editörler ve Köşe Yazarları</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary shadow-sm px-4">
            <i class="fa fa-plus me-1"></i> Yeni Ekip Üyesi
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th class="ps-4 py-3">PROFİL</th>
                            <th>İLETİŞİM / SOSYAL</th>
                            <th>YETKİ / ROL</th>
                            <th>KAYIT TARİHİ</th>
                            <th class="text-center">İŞLEMLER</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        @if($user->avatar)
                                            <img src="{{ asset('storage/'.$user->avatar) }}" class="rounded-circle border shadow-sm" width="45" height="45" style="object-fit: cover;">
                                        @else
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 45px; height: 45px;">
                                                <span class="fw-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark mb-0">{{ $user->name }}</div>
                                        @if($user->author_name)
                                            <small class="text-primary italic"><i class="fa fa-feather me-1"></i>{{ $user->author_name }}</small>
                                        @else
                                            <small class="text-muted">Takma ad belirtilmedi</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="small text-dark mb-1"><i class="fa fa-envelope me-1 text-muted"></i> {{ $user->email }}</div>
                                <div class="d-flex gap-2">
                                    @if($user->phone) <i class="fa fa-phone text-success" title="{{ $user->phone }}"></i> @endif
                                    @if($user->facebook) <i class="fab fa-facebook text-primary"></i> @endif
                                    @if($user->twitter) <i class="fab fa-twitter text-info"></i> @endif
                                    @if($user->linkedin) <i class="fab fa-linkedin text-primary"></i> @endif
                                    @if($user->youtube) <i class="fab fa-youtube text-danger"></i> @endif
                                </div>
                            </td>
                            <td>
                                @php
                                    $roleClass = [
                                        'admin' => 'bg-danger',
                                        'editor' => 'bg-warning text-dark',
                                        'columnist' => 'bg-info text-white'
                                    ][$user->role] ?? 'bg-secondary';
                                    
                                    $roleName = [
                                        'admin' => 'YÖNETİCİ',
                                        'editor' => 'EDİTÖR',
                                        'columnist' => 'KÖŞE YAZARI'
                                    ][$user->role] ?? strtoupper($user->role);
                                @endphp
                                <span class="badge {{ $roleClass }} px-3 py-2 rounded-pill shadow-xs" style="font-size: 0.7rem;">
                                    {{ $roleName }}
                                </span>
                            </td>
                            <td class="text-muted small">
                                {{ $user->created_at->translatedFormat('d M Y') }}
                            </td>
                            <td class="text-center pe-4">
                                <div class="btn-group shadow-sm rounded">
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-white border">
                                        <i class="fa fa-edit text-primary"></i>
                                    </a>
                                    
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-white border text-danger" onclick="return confirm('Bu kullanıcıyı silmek istediğinize emin misiniz? Tüm bilgileri silinecektir.')">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <button class="btn btn-sm btn-light border text-muted" disabled title="Kendinizi silemezsiniz">
                                            <i class="fa fa-lock"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($users->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .table thead th { font-size: 0.75rem; letter-spacing: 0.5px; font-weight: 700; border: none; }
    .shadow-xs { box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .btn-white { background: white; }
    .btn-white:hover { background: #f8f9fa; }
</style>
@endsection