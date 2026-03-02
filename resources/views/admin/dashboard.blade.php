@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 border-0">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Toplam Haber</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_posts']) }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-newspaper fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2 border-0">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Toplam İzlenme</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_views']) }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-eye fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2 border-0">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Kategori Sayısı</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_category'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-folder fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2 border-0">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Yazar/Editör</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_users'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-users fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4 border-0">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-primary">Son Eklenen Haberler</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Başlık</th>
                                    <th>Kategori</th>
                                    <th>Tarih</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recent_posts as $post)
                                <tr>
                                    <td><small class="fw-bold">{{ Str::limit($post->title, 50) }}</small></td>
                                    <td><span class="badge bg-light text-dark border">{{ $post->category->name ?? '-' }}</span></td>
                                    <td><small>{{ $post->created_at->diffForHumans() }}</small></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4 border-0">
                <div class="card-header py-3 bg-white text-primary fw-bold">Popüler Haberler</div>
                <div class="card-body">
                    @foreach($popular_posts as $pop)
                    <div class="mb-3 border-bottom pb-2">
                        <div class="small fw-bold">{{ $pop->title }}</div>
                        <div class="text-muted small"><i class="fa fa-eye"></i> {{ $pop->view_count }} kez okundu</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection