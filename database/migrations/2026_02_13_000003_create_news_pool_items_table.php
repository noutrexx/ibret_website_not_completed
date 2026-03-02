<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_pool_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rss_source_id')->constrained('rss_sources')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('published_post_id')->nullable()->constrained('posts')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->index();
            $table->text('summary')->nullable();
            $table->longText('content')->nullable();
            $table->string('image_url', 1500)->nullable();
            $table->string('source_url', 1500)->nullable();
            $table->string('source_guid', 1500)->nullable();
            $table->string('fingerprint', 64)->unique();
            $table->timestamp('source_published_at')->nullable()->index();
            $table->enum('status', ['draft', 'approved', 'rejected', 'published'])->default('draft')->index();
            $table->boolean('ai_processed')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_pool_items');
    }
};
