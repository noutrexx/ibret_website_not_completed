<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Users tablosuna yazar alanları ekle
        Schema::table('users', function (Blueprint $table) {
            $table->string('author_name')->nullable(); // Takma ad veya tam ad
            $table->string('avatar')->nullable(); // Yazar fotoğrafı
            $table->text('bio')->nullable(); // Kısa özgeçmiş
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('facebook')->nullable();
            $table->string('twitter')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('youtube')->nullable();
        });

        // Makaleler tablosu (User'a bağlı)
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Yazar (User) ID
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content');
            $table->integer('view_count')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('articles');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['author_name', 'avatar', 'bio', 'phone', 'website', 'facebook', 'twitter', 'linkedin', 'youtube']);
        });
    }
};