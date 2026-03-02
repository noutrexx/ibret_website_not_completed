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
    Schema::create('categories', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('parent_id')->nullable(); // Üst kategori ID'si
        $table->string('name');             // Kategori Adı
        $table->string('slug')->unique();   // URL (spor, teknoloji vb.)
        $table->string('page_title')->nullable();      // Sayfa Başlığı
        $table->text('page_description')->nullable();  // Sayfa Açıklaması
        $table->string('page_keywords')->nullable();    // Sayfa Etiketleri
        $table->integer('order')->default(0);           // Sıralama
        $table->timestamps();

        // Kendi kendine ilişki (Kategori silinirse altları sahipsiz kalmasın diye null yapıyoruz)
        $table->foreign('parent_id')->references('id')->on('categories')->onDelete('set null');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
