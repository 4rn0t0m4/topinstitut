<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AudiotelService
{
    private string $idClientCrypte;

    private string $idServiceCrypte;

    public function __construct()
    {
        $this->idClientCrypte = config('services.audiotel.id_client', 'BWIAPVJkBWEDYQBuB3MENlZiUzJWMAc7');
        $this->idServiceCrypte = config('services.audiotel.id_service', 'BWIAPVJkBWQDZgA1B28ELFY5UzdWNQcxAj0=');
    }

    /**
     * Encode un numéro de téléphone (obfuscation simple, pas cryptographique).
     */
    public static function encode(string $phone): string
    {
        $offset = 25;
        $parts = [];
        foreach (str_split($phone) as $char) {
            $parts[] = ord($char) + $offset;
        }

        return implode('_', $parts);
    }

    /**
     * Décode un numéro de téléphone encodé.
     */
    public static function decode(string $encoded): string
    {
        $parts = explode('_', $encoded);
        $phone = '';
        foreach ($parts as $code) {
            $phone .= chr((int) $code - 25);
        }

        return $phone;
    }

    /**
     * Formate un numéro FR : 0612345678 → 06.12.34.56.78
     */
    public static function format(string $phone, string $separator = '.'): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) === 10) {
            return implode($separator, str_split($phone, 2));
        }

        return $phone;
    }

    /**
     * Détecte si le user-agent est un crawler (pour SEO).
     */
    public static function isCrawler(?string $userAgent): bool
    {
        if (! $userAgent) {
            return false;
        }

        $crawlers = ['Google', 'Bingbot', 'msnbot', 'Yahoo', 'Rambler', 'bot', 'crawl', 'spider', 'AhrefsBot', 'BLEXBot'];

        foreach ($crawlers as $crawler) {
            if (stripos($userAgent, $crawler) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Appelle l'API Audiotel pour obtenir un numéro surtaxé temporaire.
     * Retourne le numéro premium ou le numéro original en fallback.
     */
    public function getEphemeralNumber(string $phone, int $etablissementId, string $pageUrl, string $ip): array
    {
        if (! config('services.audiotel.enabled', false)) {
            return ['numero' => $phone, 'premium' => false];
        }

        try {
            $destNumber = urlencode(base64_encode(serialize([$phone])));
            $idCustomer = urlencode(base64_encode(serialize(['TI-'.$etablissementId])));
            $ipEncoded = urlencode(base64_encode($ip));
            $pageEncoded = urlencode(base64_encode($pageUrl));

            $response = Http::timeout(5)->get('https://api.audiotel.me/ephemeral_number/assign', [
                'id_client' => $this->idClientCrypte,
                'id_service' => $this->idServiceCrypte,
                'dest_number' => $destNumber,
                'id_customer' => $idCustomer,
                'ip' => $ipEncoded,
                'page' => $pageEncoded,
                'format_retour' => 'xml',
                'cryptage' => 'false',
            ]);

            $xml = simplexml_load_string($response->body());

            if ($xml && ! empty((string) $xml->premium_number)) {
                return [
                    'numero' => (string) $xml->premium_number,
                    'code' => ((string) $xml->code) ?: null,
                    'premium' => true,
                    'tarif' => (string) ($xml->tarif_display ?? ''),
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Audiotel API error: '.$e->getMessage());
        }

        return ['numero' => $phone, 'premium' => false];
    }
}
