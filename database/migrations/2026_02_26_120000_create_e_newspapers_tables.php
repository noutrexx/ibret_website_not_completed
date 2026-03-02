<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('e_newspapers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->date('issue_date')->index();
            $table->string('status')->default('draft')->index(); // draft|published
            $table->text('summary')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('e_newspaper_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('e_newspaper_id')->constrained('e_newspapers')->cascadeOnDelete();
            $table->foreignId('post_id')->nullable()->constrained('posts')->nullOnDelete();
            $table->string('section')->index(); // manset,gundem,spor,ekonomi,...
            $table->unsignedInteger('position')->default(0)->index();

            // snapshot fields for archive stability
            $table->string('title');
            $table->text('summary')->nullable();
            $table->string('image')->nullable();
            $table->string('category_name')->nullable();
            $table->string('post_url')->nullable();
            $table->timestamp('post_published_at')->nullable();

            $table->timestamps();
            $table->index(['e_newspaper_id', 'section', 'position'], 'e_news_items_section_pos_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('e_newspaper_items');
        Schema::dropIfExists('e_newspapers');
    }
};

