<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Güvenli: tablo yoksa hata vermez
        Schema::dropIfExists('articles');
    }

    public function down(): void
    {
        // Geri almak istersen minimum şema ile yeniden oluşturur.
        // (Eski create_articles_table.php içindeki user alanlarını ASLA dokunmuyoruz.)
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content');
            $table->unsignedInteger('view_count')->default(0);
            $table->string('status', 20)->default('draft'); // eskiden boolean'du; artık draft/published
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
