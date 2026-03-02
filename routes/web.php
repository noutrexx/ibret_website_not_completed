<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SitemapController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\NewsPoolController;
use App\Http\Controllers\Admin\CommentController as AdminCommentController;
use App\Http\Controllers\Admin\MediaLibraryController;
use App\Http\Controllers\Front\PostShowController;
use App\Http\Controllers\Front\CommentController;
use App\Http\Controllers\Front\HomeController;
use App\Http\Controllers\Front\CategoryController as FrontCategoryController;
use App\Http\Controllers\Front\SearchController;
use App\Http\Controllers\Front\AuthorsController;
use App\Http\Controllers\Front\TagController;
use App\Http\Controllers\Front\NewspaperController;
use App\Http\Controllers\Front\ENewspaperController as FrontENewspaperController;
use App\Http\Controllers\Admin\ENewspaperController as AdminENewspaperController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/haber/{slug}', [PostShowController::class, 'legacy'])
    ->name('post.legacy');

Route::get('/kategori/{slug}', [FrontCategoryController::class, 'show'])
    ->name('category.legacy');

Route::get('/kategori/{slug}/puan-durumu', function (string $slug) {
    return redirect()->route('category.sports.standings', ['slug' => $slug], 301);
});
Route::get('/kategori/{slug}/fikstur', function (string $slug) {
    return redirect()->route('category.sports.fixtures', ['slug' => $slug], 301);
});
Route::get('/kategori/{slug}/canli-skor', function (string $slug) {
    return redirect()->route('category.sports.live', ['slug' => $slug], 301);
});
Route::get('/kategori/{slug}/doviz', function (string $slug) {
    return redirect()->route('category.economy.currencies', ['slug' => $slug], 301);
});
Route::get('/kategori/{slug}/altin', function (string $slug) {
    return redirect()->route('category.economy.gold', ['slug' => $slug], 301);
});
Route::get('/kategori/{slug}/kripto', function (string $slug) {
    return redirect()->route('category.economy.crypto', ['slug' => $slug], 301);
});
Route::get('/kategori/{slug}/borsa', function (string $slug) {
    return redirect()->route('category.economy.borsa', ['slug' => $slug], 301);
});

Route::get('/{slug}/puan-durumu', [FrontCategoryController::class, 'sportsStandings'])
    ->name('category.sports.standings');
Route::get('/{slug}/fikstur', [FrontCategoryController::class, 'sportsFixtures'])
    ->name('category.sports.fixtures');
Route::get('/{slug}/canli-skor', [FrontCategoryController::class, 'sportsLive'])
    ->name('category.sports.live');
Route::get('/{slug}/doviz', [FrontCategoryController::class, 'economyCurrencies'])
    ->name('category.economy.currencies');
Route::get('/{slug}/altin', [FrontCategoryController::class, 'economyGold'])
    ->name('category.economy.gold');
Route::get('/{slug}/kripto', [FrontCategoryController::class, 'economyCrypto'])
    ->name('category.economy.crypto');
Route::get('/{slug}/borsa', [FrontCategoryController::class, 'economyBorsa'])
    ->name('category.economy.borsa');
Route::get('/kategori/{categorySlug}/{slugKey}', function (string $categorySlug, string $slugKey) {
    return redirect()->route('post.show', ['categorySlug' => $categorySlug, 'slugKey' => $slugKey], 301);
});

Route::get('/{categorySlug}/{slugKey}', [PostShowController::class, 'show'])
    ->where('slugKey', '.*-n[0-9]+')
    ->name('post.show');

Route::get('/haberleri/{tag}', [TagController::class, 'index'])
    ->name('tag.show');

Route::get('/arama', [SearchController::class, 'index'])
    ->name('search');

Route::get('/yazarlar', [AuthorsController::class, 'index'])
    ->name('authors.index');
Route::get('/gazeteler', [NewspaperController::class, 'index'])
    ->name('newspapers.index');
Route::get('/e-gazete', [FrontENewspaperController::class, 'index'])->name('enewspapers.index');
Route::get('/e-gazete/{slug}/baski', [FrontENewspaperController::class, 'print'])->name('enewspapers.print');
Route::get('/e-gazete/{slug}', [FrontENewspaperController::class, 'show'])->name('enewspapers.show');


Route::get('/robots.txt', [SettingController::class, 'robots'])
    ->name('robots.txt');

Route::get('/sitemap.xml', [SitemapController::class, 'index']);
Route::get('/_post/next/{id}', [PostShowController::class, 'nextById'])
    ->whereNumber('id')
    ->name('post.next');
Route::get('/_post/sidebar/{id}', [PostShowController::class, 'sidebarById'])
    ->whereNumber('id')
    ->name('post.sidebar');
Route::post('/{categorySlug}/{slugKey}/yorum', [CommentController::class, 'store'])
    ->where('slugKey', '.*-n[0-9]+')
    ->name('post.comment.store')
    ->middleware('throttle:10,1');

// Admin Paneli Rotaları
Route::middleware(['auth', 'verified'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Makaleler
    Route::get('/makaleler', [ArticleController::class, 'index'])->name('admin.articles.index');
    Route::get('/makaleler/ekle', [ArticleController::class, 'create'])->name('admin.articles.create');
    Route::post('/makaleler/kaydet', [ArticleController::class, 'store'])->name('admin.articles.store');

    Route::get('/makaleler/duzenle/{id}', [ArticleController::class, 'edit'])->name('admin.articles.edit');
    Route::put('/makaleler/guncelle/{id}', [ArticleController::class, 'update'])->name('admin.articles.update');
    Route::delete('/makaleler/sil/{id}', [ArticleController::class, 'destroy'])->name('admin.articles.destroy');

    // Makaleler Çöp Kutusu
    Route::get('/makaleler/cop-kutusu', [ArticleController::class, 'trashed'])->name('admin.articles.trashed');
    Route::get('/makaleler/geri-yukle/{id}', [ArticleController::class, 'restore'])->name('admin.articles.restore');
    Route::delete('/makaleler/kalici-sil/{id}', [ArticleController::class, 'forceDelete'])->name('admin.articles.forceDelete');

    // Haberler
    Route::get('/haberler', [PostController::class, 'index'])->name('admin.posts.index');
    Route::get('/haberler/ekle', [PostController::class, 'create'])->name('admin.posts.create');
    Route::post('/haberler/kaydet', [PostController::class, 'store'])->name('admin.posts.store');
    Route::get('/haberler/duzenle/{id}', [PostController::class, 'edit'])->name('admin.posts.edit');
    Route::put('/haberler/guncelle/{id}', [PostController::class, 'update'])->name('admin.posts.update');
    Route::delete('/haberler/sil/{id}', [PostController::class, 'destroy'])->name('admin.posts.destroy');
    Route::get('/haberler/cop-kutusu', [PostController::class, 'trashed'])->name('admin.posts.trashed');
    Route::get('/haberler/geri-yukle/{id}', [PostController::class, 'restore'])->name('admin.posts.restore');
    Route::delete('/haberler/kalici-sil/{id}', [PostController::class, 'forceDelete'])->name('admin.posts.forceDelete');
    Route::get('/haber-havuzu', [NewsPoolController::class, 'index'])->name('admin.news-pool.index');
    Route::post('/haber-havuzu/rss-cek', [NewsPoolController::class, 'ingest'])->name('admin.news-pool.ingest');
    Route::post('/haber-havuzu/toplu-islem', [NewsPoolController::class, 'bulkAction'])->name('admin.news-pool.bulk');
    Route::post('/haber-havuzu/taslaklari-temizle', [NewsPoolController::class, 'clearDrafts'])->name('admin.news-pool.clear-drafts');
    Route::get('/haber-havuzu/{id}/duzenle', [NewsPoolController::class, 'edit'])->name('admin.news-pool.edit');
    Route::put('/haber-havuzu/{id}', [NewsPoolController::class, 'update'])->name('admin.news-pool.update');
    Route::post('/haber-havuzu/{id}/ai-duzenle', [NewsPoolController::class, 'aiRewrite'])->name('admin.news-pool.ai.rewrite');
    Route::post('/haber-havuzu/{id}/yayina-al', [NewsPoolController::class, 'approve'])->name('admin.news-pool.approve');
    Route::post('/haber-havuzu/{id}/reddet', [NewsPoolController::class, 'reject'])->name('admin.news-pool.reject');
    Route::get('/e-gazeteler', [AdminENewspaperController::class, 'index'])->name('admin.e-newspapers.index');
    Route::post('/e-gazeteler/olustur', [AdminENewspaperController::class, 'generate'])->name('admin.e-newspapers.generate');
    Route::get('/e-gazeteler/{id}/duzenle', [AdminENewspaperController::class, 'edit'])->name('admin.e-newspapers.edit');
    Route::put('/e-gazeteler/{id}', [AdminENewspaperController::class, 'update'])->name('admin.e-newspapers.update');
    Route::post('/e-gazeteler/{id}/yayinla', [AdminENewspaperController::class, 'publish'])->name('admin.e-newspapers.publish');
    Route::post('/e-gazeteler/{id}/taslak', [AdminENewspaperController::class, 'unpublish'])->name('admin.e-newspapers.unpublish');
    Route::delete('/e-gazeteler/{id}', [AdminENewspaperController::class, 'destroy'])->name('admin.e-newspapers.destroy');
    Route::get('/medya-kutuphanesi', [MediaLibraryController::class, 'index'])->name('admin.media.index');
    Route::post('/medya-kutuphanesi/yukle', [MediaLibraryController::class, 'upload'])->name('admin.media.upload');
    Route::post('/medya-kutuphanesi/{id}/favori', [MediaLibraryController::class, 'toggleFavorite'])->name('admin.media.favorite');
    Route::get('/medya-kutuphanesi/ucretsiz-ara', [MediaLibraryController::class, 'freeSearch'])->name('admin.media.free-search');
    Route::post('/medya-kutuphanesi/ucretsiz-ice-aktar', [MediaLibraryController::class, 'importRemote'])->name('admin.media.import-remote');
    Route::get('/yorumlar', [AdminCommentController::class, 'index'])->name('admin.comments.index');
    Route::post('/yorumlar/{id}/onayla', [AdminCommentController::class, 'approve'])->name('admin.comments.approve');
    Route::post('/yorumlar/{id}/reddet', [AdminCommentController::class, 'reject'])->name('admin.comments.reject');
    Route::delete('/yorumlar/{id}', [AdminCommentController::class, 'destroy'])->name('admin.comments.destroy');

    Route::resource('users', UserController::class)->names('admin.users');

    // Kategoriler
    Route::get('/kategoriler', [CategoryController::class, 'index'])->name('admin.categories.index');
    Route::get('/kategoriler/ekle', [CategoryController::class, 'create'])->name('admin.categories.create');
    Route::post('/kategoriler/kaydet', [CategoryController::class, 'store'])->name('admin.categories.store');
    Route::get('/kategoriler/duzenle/{id}', [CategoryController::class, 'edit'])->name('admin.categories.edit');
    Route::put('/kategoriler/guncelle/{id}', [CategoryController::class, 'update'])->name('admin.categories.update');
    Route::delete('/kategoriler/sil/{id}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');

    Route::get('/ayarlar', [SettingController::class, 'index'])->name('admin.settings.index');
    Route::post('/ayarlar/guncelle', [SettingController::class, 'update'])->name('admin.settings.update');
    Route::post('/ayarlar/seo/sitemap-ping', [SettingController::class, 'pingSitemap'])->name('admin.settings.seo.sitemapPing');
});

require __DIR__.'/auth.php';

Route::get('/{parentSlug}/{slug}', [FrontCategoryController::class, 'showChild'])
    ->name('category.child.show');

Route::get('/{slug}', [FrontCategoryController::class, 'show'])
    ->name('category.show');
