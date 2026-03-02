<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE `posts`
            MODIFY COLUMN `type` ENUM('normal','manset','surmanset','top_manset','spor_manset','gizli')
            NOT NULL DEFAULT 'normal'
        ");
    }

    public function down(): void
    {
        DB::statement("
            UPDATE `posts`
            SET `type` = 'normal'
            WHERE `type` = 'spor_manset'
        ");

        DB::statement("
            ALTER TABLE `posts`
            MODIFY COLUMN `type` ENUM('normal','manset','surmanset','top_manset','gizli')
            NOT NULL DEFAULT 'normal'
        ");
    }
};
