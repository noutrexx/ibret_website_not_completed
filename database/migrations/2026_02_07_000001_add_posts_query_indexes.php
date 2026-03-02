<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->index('content_kind');
            $table->index('status');
            $table->index('published_at');
            $table->index('deleted_at');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['content_kind']);
            $table->dropIndex(['status']);
            $table->dropIndex(['published_at']);
            $table->dropIndex(['deleted_at']);
            $table->dropIndex(['type']);
        });
    }
};
