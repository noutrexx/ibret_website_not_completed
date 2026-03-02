<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (!Schema::hasColumn('posts', 'is_breaking')) {
                $table->boolean('is_breaking')->default(false)->after('type')->index();
            }

            if (!Schema::hasColumn('posts', 'photo_gallery')) {
                $table->json('photo_gallery')->nullable()->after('video_url');
            }

            if (!Schema::hasColumn('posts', 'video_gallery')) {
                $table->json('video_gallery')->nullable()->after('photo_gallery');
            }
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (Schema::hasColumn('posts', 'video_gallery')) {
                $table->dropColumn('video_gallery');
            }

            if (Schema::hasColumn('posts', 'photo_gallery')) {
                $table->dropColumn('photo_gallery');
            }

            if (Schema::hasColumn('posts', 'is_breaking')) {
                $table->dropIndex(['is_breaking']);
                $table->dropColumn('is_breaking');
            }
        });
    }
};
