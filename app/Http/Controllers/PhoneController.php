<?php

namespace App\Http\Controllers;

use App\Models\Establishment;
use App\Services\AudiotelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhoneController extends Controller
{
    public function reveal(Request $request, AudiotelService $audiotel): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
            'etablissement_id' => 'required|integer',
        ]);

        $phone = AudiotelService::decode($request->input('phone'));
        $establishment = Establishment::findOrFail($request->input('etablissement_id'));

        $isMobile = preg_match('/Mobile|Android|iPhone|iPad/i', $request->userAgent() ?? '');

        // Bypass audiotel pour les crawlers ET les établissements Premium actifs.
        if (AudiotelService::isCrawler($request->userAgent()) || $establishment->is_premium) {
            return response()->json([
                'phone' => AudiotelService::format($phone),
                'tel' => preg_replace('/[^0-9+]/', '', $phone),
                'premium' => false,
                'mobile' => (bool) $isMobile,
            ]);
        }

        $result = $audiotel->getEphemeralNumber(
            $phone,
            $establishment->id,
            url($establishment->url),
            $request->ip()
        );

        return response()->json([
            'phone' => AudiotelService::format($result['numero']),
            'tel' => preg_replace('/[^0-9+]/', '', $result['numero']),
            'code' => $result['code'] ?? null,
            'premium' => $result['premium'],
            'tarif' => $result['tarif'] ?? '',
            'mobile' => (bool) $isMobile,
        ]);
    }
}
