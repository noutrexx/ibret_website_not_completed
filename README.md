İbret Haber, güncel haberlerin ve köşe yazılarının yayınlandığı, kategori bazlı içerik yönetimine sahip bir web uygulamasıdır. Laravel framework'ünün en güncel sürümü kullanılarak geliştirilmekte olup; yazar profilleri, makale yönetimi ve dinamik site ayarları gibi özellikler barındırır.

İbret Haber Portalı

Bu proje, Laravel 12 kullanılarak geliştirilen modern bir haber ve içerik yönetim sistemidir. Güncel haber akışı, köşe yazarları sistemi ve SEO odaklı bir yapı hedeflenmektedir.

 Durum: Bu proje şu anda geliştirme aşamasındadır (WIP) ve henüz tamamlanmamıştır. Özellikler zamanla eklenecektir.

 Özellikler (Planlanan ve Mevcut)

-    Dinamik Haber ve Makale Yönetimi
-    Yazar (Köşe Yazarı) Profilleri ve Listeleme
-    Kategori Bazlı İçerik Yapısı
-    Gelişmiş SEO ve Site Ayarları Yönetimi
-    Responsive (Mobil Uyumlu) Arayüz
-    *Admin Paneli (Geliştiriliyor)*
-    *Yorum Sistemi (Geliştiriliyor)*

Kurulum (Geliştiriciler İçin)

Kullanılan Teknolojiler (Tech Stack)

Bu proje modern bir web mimarisi üzerine inşa edilmiştir:

 Core: [Laravel 12](https://laravel.com/) (PHP 8.3)
 Database: [Oracle Database XE](https://www.oracle.com/database/technologies/appdev/xe.html)
 Database Driver: [Yajra Laravel-OCI8](https://github.com/yajra/laravel-oci8)
 Frontend: [Tailwind CSS](https://tailwindcss.com/) & Blade Templating
 Testing: [Pest](https://pestphp.com/) & PHPUnit

Projeyi yerel ortamınızda çalıştırmak için:

1.  Repoyu klonlayın.
2.  Bağımlılıkları yükleyin:
    ```bash
    composer install
    npm install
    ```
3.  `.env` dosyasını ayarlayın ve veritabanını oluşturun.
4.  Kurulumu tamamlayın:
    ```bash
    composer run setup
    ```
5.  Sunucuyu başlatın:
    ```bash
    php artisan serve
    ```

Lisans

Bu proje açık kaynaklıdır. MIT license altında lisanslanmıştır.
