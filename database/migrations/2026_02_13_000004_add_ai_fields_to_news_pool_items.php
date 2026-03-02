<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_pool_items', function (Blueprint $table) {
            if (!Schema::hasColumn('news_pool_items', 'raw_title')) {
                $table->string('raw_title')->nullable()->after('published_post_id');
            }
            if (!Schema::hasColumn('news_pool_items', 'raw_summary')) {
                $table->text('raw_summary')->nullable()->after('raw_title');
            }
            if (!Schema::hasColumn('news_pool_items', 'raw_content')) {
                $table->longText('raw_content')->nullable()->after('raw_summary');
            }
            if (!Schema::hasColumn('news_pool_items', 'ai_title')) {
                $table->string('ai_title')->nullable()->after('content');
            }
            if (!Schema::hasColumn('news_pool_items', 'ai_summary')) {
                $table->text('ai_summary')->nullable()->after('ai_title');
            }
            if (!Schema::hasColumn('news_pool_items', 'ai_content')) {
                $table->longText('ai_content')->nullable()->after('ai_summary');
            }
            if (!Schema::hasColumn('news_pool_items', 'ai_keywords')) {
                $table->string('ai_keywords', 1000)->nullable()->after('ai_content');
            }
            if (!Schema::hasColumn('news_pool_items', 'ai_status')) {
                $table->string('ai_status', 30)->nullable()->after('ai_keywords')->index();
            }
            if (!Schema::hasColumn('news_pool_items', 'ai_error')) {
                $table->text('ai_error')->nullable()->after('ai_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('news_pool_items', function (Blueprint $table) {
            foreach ([
                'raw_title',
                'raw_summary',
                'raw_content',
                'ai_title',
                'ai_summary',
                'ai_content',
                'ai_keywords',
                'ai_status',
                'ai_error',
            ] as $column) {
                if (Schema::hasColumn('news_pool_items', $column)) {
                    if ($column === 'ai_status') {
                        $table->dropIndex(['ai_status']);
                    }
                    $table->dropColumn($column);
                }
            }
        });
    }
};
