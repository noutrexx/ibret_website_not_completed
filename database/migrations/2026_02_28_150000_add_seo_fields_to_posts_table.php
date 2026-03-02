<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (!Schema::hasColumn('posts', 'seo_title')) {
                $table->string('seo_title', 190)->nullable()->after('title');
            }

            if (!Schema::hasColumn('posts', 'seo_description')) {
                $table->text('seo_description')->nullable()->after('summary');
            }

            if (!Schema::hasColumn('posts', 'focus_keywords')) {
                $table->string('focus_keywords', 700)->nullable()->after('seo_description');
            }

            if (!Schema::hasColumn('posts', 'seo_entities')) {
                $table->json('seo_entities')->nullable()->after('focus_keywords');
            }

            if (!Schema::hasColumn('posts', 'schema_jsonld')) {
                $table->longText('schema_jsonld')->nullable()->after('seo_entities');
            }
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $drops = [];

            if (Schema::hasColumn('posts', 'schema_jsonld')) {
                $drops[] = 'schema_jsonld';
            }
            if (Schema::hasColumn('posts', 'seo_entities')) {
                $drops[] = 'seo_entities';
            }
            if (Schema::hasColumn('posts', 'focus_keywords')) {
                $drops[] = 'focus_keywords';
            }
            if (Schema::hasColumn('posts', 'seo_description')) {
                $drops[] = 'seo_description';
            }
            if (Schema::hasColumn('posts', 'seo_title')) {
                $drops[] = 'seo_title';
            }

            if (!empty($drops)) {
                $table->dropColumn($drops);
            }
        });
    }
};
