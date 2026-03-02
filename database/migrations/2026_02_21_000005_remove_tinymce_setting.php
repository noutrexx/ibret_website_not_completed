<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->where('key', 'tinymce_api_key')->delete();
    }

    public function down(): void
    {
        // TinyMCE kaldirildi; geri alma uygulanmiyor.
    }
};

