<?php

namespace App\Services;

use Illuminate\Support\Str;

class SlugService
{
    public static function generate(string $text): string
    {
        $slug = Str::ascii($text, 'fr');
        $slug = strtolower($slug);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        return $slug;
    }
}
