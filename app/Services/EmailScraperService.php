<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmailScraperService
{
    private const IGNORED_DOMAINS = [
        'example.com', 'example.org', 'test.com', 'sentry.io',
        'wixpress.com', 'wordpress.com', 'googleapis.com', 'sentry-next.wixpress.com',
        'gstatic.com', 'fontawesome.com', 'cloudfront.net',
    ];

    private const IGNORED_PREFIXES = [
        'noreply', 'no-reply', 'webmaster', 'admin@wordpress',
        'support@wix', 'info@example', 'donotreply',
    ];

    /**
     * Common French contact-page paths to try if the homepage yields nothing.
     */
    private const CONTACT_PATHS = ['/contact', '/contactez-nous', '/nous-contacter', '/mentions-legales'];

    public function findEmail(string $url): ?string
    {
        $url = $this->normalizeUrl($url);
        if (! $url) {
            return null;
        }

        // 1. Try homepage
        $email = $this->scrapeUrl($url);
        if ($email) {
            return $email;
        }

        // 2. Fallback : common contact pages
        $base = rtrim($url, '/');
        foreach (self::CONTACT_PATHS as $path) {
            $email = $this->scrapeUrl($base.$path);
            if ($email) {
                return $email;
            }
        }

        return null;
    }

    private function scrapeUrl(string $url): ?string
    {
        try {
            $response = Http::timeout(8)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; TopInstitut/1.0)'])
                ->get($url);

            if (! $response->ok()) {
                return null;
            }

            $html = $response->body();

            // Decode common HTML obfuscations (&#64; for @, etc.)
            $html = html_entity_decode($html);

            // 1. mailto: links first (most reliable signal)
            preg_match_all('/mailto:([a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})/i', $html, $mailtoMatches);

            // 2. Plain email patterns
            preg_match_all('/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/', $html, $allMatches);

            $candidates = array_merge($mailtoMatches[1] ?? [], $allMatches[0] ?? []);
            $candidates = array_unique(array_map('strtolower', $candidates));

            foreach ($candidates as $email) {
                if ($this->isValidEmail($email)) {
                    return $email;
                }
            }
        } catch (\Throwable $e) {
            Log::debug('Email scraping failed for '.$url.': '.$e->getMessage());
        }

        return null;
    }

    private function isValidEmail(string $email): bool
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $domain = substr($email, strpos($email, '@') + 1);

        foreach (self::IGNORED_DOMAINS as $ignored) {
            if (str_contains($domain, $ignored)) {
                return false;
            }
        }

        foreach (self::IGNORED_PREFIXES as $prefix) {
            if (str_starts_with($email, $prefix)) {
                return false;
            }
        }

        // Skip image/asset filenames misread as emails (sentry@2x.png etc.)
        if (preg_match('/\.(png|jpg|jpeg|gif|svg|webp|css|js|woff2?)$/i', $email)) {
            return false;
        }

        return true;
    }

    private function normalizeUrl(string $url): ?string
    {
        $url = trim($url);
        if (! $url) {
            return null;
        }

        if (! preg_match('/^https?:\/\//i', $url)) {
            $url = 'https://'.$url;
        }

        return filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
    }
}
