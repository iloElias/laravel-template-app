<?php

namespace App\Models;

class Locale
{
    protected static array $locales;

    public static function all()
    {
        if (isset(self::$locales)) {
            return self::$locales;
        }
        $locales = [];
        $path = base_path('lang');
        $directories = array_filter(glob("{$path}/*"), 'is_dir');
        foreach ($directories as $directory) {
            $locale = basename($directory);
            if (is_dir($directory)) {
                $locales[] = $locale;
            }
        }
        self::$locales = $locales;

        return self::$locales;
    }

    public static function default()
    {
        return config('app.fallback_locale');
    }

    public static function available($locale)
    {
        return in_array($locale, self::$locales);
    }

    public static function format(string $locale)
    {
        return str_replace(
            '-',
            '_',
            $locale
        );
    }

    public static function resolve(string $candidate): string
    {
        $fallback = config('app.fallback_locale');
        $path = base_path("lang/{$candidate}");

        if (is_dir($path)) {
            return $candidate;
        }

        $base = strtok($candidate, '_');
        if (is_dir(base_path("lang/{$base}"))) {
            return $base;
        }

        return $fallback;
    }
}
