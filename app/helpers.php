<?php

use App\Models\Setting;
use Illuminate\Support\Str;

if (!function_exists('setting')) {
    function setting($key, $default = null)
    {
        return cache()->rememberForever(
            'setting_' . $key,
            fn () => Setting::where('key', $key)->value('value') ?? $default
        );
    }
}

if (!function_exists('team_logo_url')) {
    function team_logo_url(?string $teamName): ?string
    {
        $name = trim((string) $teamName);
        if ($name === '') {
            return null;
        }

        $map = [
            'ı' => 'i', 'İ' => 'i',
            'ş' => 's', 'Ş' => 's',
            'ğ' => 'g', 'Ğ' => 'g',
            'ü' => 'u', 'Ü' => 'u',
            'ö' => 'o', 'Ö' => 'o',
            'ç' => 'c', 'Ç' => 'c',
        ];

        $normalized = strtr($name, $map);
        $slug = Str::slug($normalized);
        if ($slug === '') {
            return null;
        }

        $extensions = ['png', 'webp', 'jpg', 'jpeg', 'svg'];
        foreach ($extensions as $ext) {
            $relativePath = 'images/teams/' . $slug . '.' . $ext;
            if (file_exists(public_path($relativePath))) {
                return asset($relativePath);
            }
        }

        return null;
    }
}
