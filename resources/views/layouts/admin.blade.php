<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Haber Yönetim Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --sidebar-bg: #2c3e50; --main-bg: #f8f9fa; }
        body { background-color: var(--main-bg); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { width: 260px; height: 100vh; position: fixed; background: var(--sidebar-bg); color: white; transition: all 0.3s; }
        .sidebar .nav-link { color: #bdc3c7; padding: 12px 20px; font-size: 15px; border-left: 4px solid transparent; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: #34495e; color: white; border-left: 4px solid #3498db; }
        .main-content { margin-left: 260px; padding: 20px; }
        .top-nav { background: white; padding: 15px; border-bottom: 1px solid #e0e0e0; margin-bottom: 25px; }
        .card-stats { border: none; border-radius: 10px; transition: transform 0.2s; }
        .card-stats:hover { transform: translateY(-5px); }
    </style>
</head>
<body>

<div class="sidebar shadow">
    <div class="p-4 text-center border-bottom border-secondary">
        <h4 class="fw-bold mb-0">HABER PANEL</h4>
    </div>
<nav class="nav flex-column mt-3">
    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" 
       href="{{ route('admin.dashboard') }}">
        <i class="fa fa-tachometer-alt me-2"></i> Anasayfa
    </a>

    <a class="nav-link {{ request()->routeIs('admin.posts.index') ? 'active' : '' }}" 
       href="{{ route('admin.posts.index') }}">
        <i class="fa fa-newspaper me-2"></i> Haberler
    </a>
    <a class="nav-link {{ request()->routeIs('admin.news-pool.*') ? 'active' : '' }}"
       href="{{ route('admin.news-pool.index') }}">
        <i class="fa fa-rss me-2"></i> Haber Havuzu
    </a>
    <a class="nav-link {{ request()->routeIs('admin.e-newspapers.*') ? 'active' : '' }}"
       href="{{ route('admin.e-newspapers.index') }}">
        <i class="fa fa-book-open me-2"></i> E-Gazeteler
    </a>

    <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" 
       href="{{ route('admin.categories.index') }}">
        <i class="fa fa-layer-group me-2"></i> Kategoriler
    </a>

    <a class="nav-link {{ request()->routeIs('admin.articles.index') ? 'active' : '' }}"  href="{{ route('admin.articles.index') }}">
        <i class="fa fa-edit me-2"></i> Makaleler</a>



    <a class="nav-link" href="#"><i class="fa fa-images me-2"></i> Foto Galeri</a>
    <a class="nav-link" href="#"><i class="fa fa-video me-2"></i> Videolar</a>
    
    @if(auth()->user()->role == 'admin')
<a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" 
   href="{{ route('admin.users.index') }}">
    <i class="fa fa-users me-2"></i> Yöneticiler
</a>        
         <a class="nav-link {{ request()->routeIs('admin.settings.index') ? 'active' : '' }}"  href="{{ route('admin.settings.index') }}">
        <i class="fa fa-cog me-2"></i> Ayarlar</a>   
        
        <a class="nav-link {{ request()->routeIs('admin.comments.*') ? 'active' : '' }}" href="{{ route('admin.comments.index') }}"><i class="fa fa-comments me-2"></i> Yorumlar</a>
        <a class="nav-link" href="#"><i class="fa fa-envelope me-2"></i> Gelen Kutusu</a>
        <a class="nav-link" href="#"><i class="fa fa-ad me-2"></i> Reklam Yönetimi</a>
    @endif
</nav>
</div>

<div class="main-content">
    <div class="top-nav shadow-sm d-flex justify-content-between align-items-center rounded">
        <h5 class="mb-0 text-secondary">Yönetim Paneli</h5>
        <div class="dropdown">
            <button class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fa fa-user-circle me-1"></i> {{ Auth::user()->name }}
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#">Profil</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="dropdown-item text-danger">Çıkış Yap</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>

    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
