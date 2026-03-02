<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('category_id')->constrained()->onDelete('cascade'); // Kategori bağlantısı
        $table->foreignId('user_id')->constrained()->onDelete('cascade');     // Ekleyen yazar/admin
        
        $table->string('title');        // Başlık
        $table->string('slug')->unique(); // URL dostu başlık
        $table->text('summary')->nullable(); // Özet Giriniz alanı
        $table->longText('content');    // İçeriğiniz (CKEditor alanı)
        
        // Haber Türü (Senin istediğin 5 seçenek)
        $table->enum('type', ['normal', 'manset', 'surmanset', 'top_manset', 'gizli'])->default('normal');
        
        $table->string('image')->nullable();      // Ana Resim
        $table->string('video_url')->nullable();  // Video Galeri bağlantısı için
        $table->string('city')->nullable();       // Şehir
        
        $table->integer('view_count')->default(0); // Görüntülenme sayısı
        $table->timestamp('published_at')->nullable(); // Yayınlama Tarihi
        
        $table->softDeletes(); // Çöp kutusu özelliği
        $table->timestamps();  // Güncellenme ve Oluşturulma tarihi
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
