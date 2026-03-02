<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('disk', 30)->default('public');
            $table->string('path', 191)->unique();
            $table->string('original_name', 255)->nullable();
            $table->string('title', 255)->nullable();
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('alt_text', 255)->nullable();
            $table->string('source_provider', 60)->nullable();
            $table->string('source_url', 1500)->nullable();
            $table->string('credit', 255)->nullable();
            $table->boolean('is_favorite')->default(false)->index();
            $table->string('hash', 64)->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['created_at', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_assets');
    }
};
