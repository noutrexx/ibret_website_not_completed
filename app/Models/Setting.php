<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    // Ayarı hızlıca çekmek için yardımcı: Setting::get('site_title')
    public static function get($key, $default = null)
    {
        return cache()->rememberForever(
            'setting_' . $key,
            fn () => self::where('key', $key)->value('value') ?? $default
        );
    }
}
