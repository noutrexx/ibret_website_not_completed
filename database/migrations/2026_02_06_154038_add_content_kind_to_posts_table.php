<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // haber mi makale mi?
            $table->string('content_kind', 20)->default('news')->after('content'); 
            // Article tarafındaki status mantığı için (draft/published)
            $table->string('status', 20)->default('draft')->after('content_kind');

            // category_id makalelerde nullable olabilsin istiyorsan:
            $table->unsignedBigInteger('category_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['content_kind', 'status']);
            // category_id eski haline döndürmek istersen:
            // $table->unsignedBigInteger('category_id')->nullable(false)->change();
        });
    }
};
