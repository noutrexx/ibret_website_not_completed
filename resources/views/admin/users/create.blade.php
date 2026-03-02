@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-md-11 mx-auto">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="fw-bold text-dark mb-0"><i class="fa fa-user-plus me-2 text-primary"></i> Yeni Ekip Üyesi Ekle</h3>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-light border shadow-sm px-4">
                        <i class="fa fa-arrow-left me-1"></i> Listeye Dön
                    </a>
                </div>

                <div class="row g-4">
                    <!-- Sol Kolon: Temel ve İletişim Bilgileri -->
                    <div class="col-lg-7">
                        <div class="card shadow-sm border-0 rounded-4 mb-4">
                            <div class="card-header bg-white py-3 border-0">
                                <h6 class="m-0 fw-bold text-dark"><i class="fa fa-id-card me-2 text-primary"></i> Hesap ve İletişim Bilgileri</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold small text-muted">Ad Soyad</label>
                                        <input type="text" name="name" class="form-control bg-light border-0 py-2" placeholder="Gerçek ad soyad" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold small text-muted">Kalem Adı (Takma İsim)</label>
                                        <input type="text" name="author_name" class="form-control bg-light border-0 py-2" placeholder="Sitede görünecek isim">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold small text-muted">E-Posta Adresi</label>
                                        <input type="email" name="email" class="form-control bg-light border-0 py-2" placeholder="ornek@site.com" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold small text-muted">Şifre</label>
                                        <input type="password" name="password" class="form-control bg-light border-0 py-2" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold small text-muted">Cep Telefonu</label>
                                        <input type="text" name="phone" class="form-control bg-light border-0 py-2" placeholder="05xx xxx xx xx">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold small text-muted">Web Sitesi</label>
                                        <input type="url" name="website" class="form-control bg-light border-0 py-2" placeholder="https://...">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold small text-muted">Kullanıcı Rolü</label>
                                        <select name="role" class="form-select bg-light border-0 py-2" required>
                                            <option value="editor">Editör</option>
                                            <option value="admin">Yönetici</option>
                                            <option value="columnist">Köşe Yazarı</option>
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
                                            <input type="url" name="facebook" class="form-control bg-light border-0" placeholder="Facebook URL">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="fab fa-twitter text-info"></i></span>
                                            <input type="url" name="twitter" class="form-control bg-light border-0" placeholder="Twitter URL">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="fab fa-linkedin text-primary"></i></span>
                                            <input type="url" name="linkedin" class="form-control bg-light border-0" placeholder="Linkedin URL">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0"><i class="fab fa-youtube text-danger"></i></span>
                                            <input type="url" name="youtube" class="form-control bg-light border-0" placeholder="Youtube URL">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sağ Kolon: Profil Detayları -->
                    <div class="col-lg-5">
                        <div class="card shadow-sm border-0 rounded-4 mb-4">
                            <div class="card-header bg-white py-3 border-0">
                                <h6 class="m-0 fw-bold text-dark"><i class="fa fa-user-circle me-2 text-primary"></i> Profil Detayları</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-4">
                                    <label class="form-label fw-bold small text-muted">Profil / Yazar Fotoğrafı</label>
                                    <input type="file" name="avatar" class="form-control bg-light border-0">
                                    <div class="form-text small">Önerilen boyut: 500x500px</div>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label fw-bold small text-muted">Biyografi</label>
                                    <textarea name="bio" class="form-control bg-light border-0" rows="8" placeholder="Kullanıcı hakkında kısa özgeçmiş veya tanıtım yazısı..."></textarea>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-3 shadow-sm fw-bold rounded-3">
                            <i class="fa fa-save me-2"></i> KULLANICIYI OLUŞTUR
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection