<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('group', 50)->default('general')->after('value'); // general, seo, social...
            $table->string('type', 30)->default('text')->after('group');     // text, textarea, bool, color, image, json...
            $table->boolean('is_public')->default(false)->after('type');     // frontend erişsin mi?
            $table->boolean('autoload')->default(true)->after('is_public');  // cache'e otomatik alınsın mı?
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['group', 'type', 'is_public', 'autoload']);
        });
    }
};
