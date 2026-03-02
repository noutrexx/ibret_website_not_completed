<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rss_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('feed_url', 240)->unique();
            $table->string('source_domain')->nullable()->index();
            $table->foreignId('default_category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('fetch_interval_minutes')->default(15);
            $table->timestamp('last_fetched_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rss_sources');
    }
};
