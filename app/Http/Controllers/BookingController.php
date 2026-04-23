<?php

namespace App\Http\Controllers;

use App\Models\Establishment;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function store(Request $request, Establishment $establishment, BookingService $bookings)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'requested_date' => 'required|date|after_or_equal:today',
            'requested_time' => 'required|string|max:20',
            'requested_service' => 'nullable|string|max:255',
            'content' => 'nullable|string|max:2000',
        ]);

        $bookings->create($establishment, $validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Demande envoyée.']);
        }

        return back()->with('success', 'Votre demande de RDV a bien été envoyée.');
    }
}
