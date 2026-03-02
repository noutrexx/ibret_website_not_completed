<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('posts', 'tags')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->string('tags', 1000)->nullable()->after('summary');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('posts', 'tags')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->dropColumn('tags');
            });
        }
    }
};

