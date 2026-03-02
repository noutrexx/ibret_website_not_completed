<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('e_newspaper_items', function (Blueprint $table) {
            $table->boolean('show_image')->default(true)->after('image');
        });
    }

    public function down(): void
    {
        Schema::table('e_newspaper_items', function (Blueprint $table) {
            $table->dropColumn('show_image');
        });
    }
};

