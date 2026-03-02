<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->updateOrInsert(
            ['key' => 'post_stream_enabled'],
            [
                'value' => '1',
                'group' => 'general',
                'type' => 'bool',
                'is_public' => true,
                'autoload' => true,
            ]
        );
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'post_stream_enabled')->delete();
    }
};

