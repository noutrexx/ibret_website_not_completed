@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-md-11 mx-auto">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="fw-bold text-dark mb-0"><i class="fa fa-user-edit me-2 text-primary"></i> Kullanıcıyı Düzenle</h3>
                        <p class="text-muted small mb-0">{{ $user->name }} profilini güncelliyorsunuz.</p>
                    </div>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-light border shadow-sm px-4">
                        <i class="fa fa-arrow-left me-1"></i> Listeye Dön
                    </a>
                </div>

                @if(session('success'))
                    <div class="alert alert-success border-0 shadow-sm mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="row g-4">
                    <!-- Sol Kolon -->
                    <div class="col-lg-7">
                        <div class="card shadow-sm border-0 rounded-4 mb-4">
                            <div class="card-header bg-white py-3 border-0">
                                <h6 class="m-0 fw-bold text-dark"><i class="fa fa-id-card me-2 text-primary"></i> Hesap ve İletişim Bilgileri</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold small text-muted">Ad Soyad</label>
                                        <input type="text" name="name" class="form-control bg-light border-0 py-2" value="{{ $user->name }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold small text-muted">Kalem Adı (Takma İsim)</label>
                                        <input type="text" name="author_name" class="form-control bg-light border-0 py-2" value="{{ $user->author_name }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold small text-muted">E-Posta Adresi</label>
                                        <input type="email" name="email" class="form-control bg-light border-0 py-2" value="{{ $user->email }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold small text-muted">Yeni Şifre (Boş Bırakılabilir)</label>
                                        <input type="password" name="password" class="form-control bg-light border-0 py-2">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold small text-muted">Cep Telefonu</label>
                                        <input type="text" name="phone" class="form-control bg-light border-0 py-2" value="{{ $user->phone }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold small text-muted">Web Sitesi</label>
                                        <input type="url" name="website" class="form-control bg-light border-0 py-2" value="{{ $user->website }}">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold small text-muted">Kullanıcı Rolü</label>
                                        <select name="role" class="form-select bg-light border-0 py-2" required>
                                            <option value="editor" {{ $user->role == 'editor' ? 'selected' : '' }}>Editör</option>
                                            <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Yönetici</option>
                                            <option value="columnist" {{ $user->role == 'columnist' ? 'selected' : '' }}>Köşe Yazarı</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm border-0 rounded-4">
                            <div class="card-header bg-white py-3 border-0">
                                <h6 class="m-0 fw-bold text-dark"><i class="fa fa-share-alt me-2 text-primary"></i> Sosyal Medya Linkleri</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="fab fa-facebook text-primary"></i></span>
                                            <input type="url" name="facebook" class="form-control bg-light border-0" value="{{ $user->facebook }}" placeholder="Facebook URL">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="fab fa-twitter text-info"></i></span>
                                            <input type="url" name="twitter" class="form-control bg-light border-0" value="{{ $user->twitter }}" placeholder="Twitter URL">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="fab fa-linkedin text-primary"></i></span>
                                            <input type="url" name="linkedin" class="form-control bg-light border-0" value="{{ $user->linkedin }}" placeholder="Linkedin URL">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="fab fa-youtube text-danger"></i></span>
                                            <input type="url" name="youtube" class="form-control bg-light border-0" value="{{ $user->youtube }}" placeholder="Youtube URL">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sağ Kolon -->
                    <div class="col-lg-5">
                        <div class="card shadow-sm border-0 rounded-4 mb-4">
                            <div class="card-header bg-white py-3 border-0">
                                <h6 class="m-0 fw-bold text-dark"><i class="fa fa-user-circle me-2 text-primary"></i> Profil Detayları</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-4 text-center">
                                    <img src="{{ $user->avatar ? asset('storage/'.$user->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=random' }}" 
                                         class="rounded-circle border mb-3 shadow-sm" width="120" height="120">
                                    <input type="file" name="avatar" class="form-control bg-light border-0">
                                </div>
                                <div class="mb-0">
                                    <label class="form-label fw-bold small text-muted">Biyografi</label>
                                    <textarea name="bio" class="form-control bg-light border-0" rows="8" placeholder="Özgeçmiş...">{{ $user->bio }}</textarea>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-3 shadow-sm fw-bold rounded-3">
                            <i class="fa fa-sync me-2"></i> DEĞİŞİKLİKLERİ KAYDET
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection