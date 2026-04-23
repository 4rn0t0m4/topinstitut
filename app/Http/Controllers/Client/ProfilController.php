<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfilController extends Controller
{
    public function edit(Request $request)
    {
        return view('client.profil.edit', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users,username,'.$request->user()->id,
            'last_name' => 'nullable|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'gender' => 'nullable|in:male,female',
            'address' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:5',
            'city' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
        ]);

        $request->user()->update($validated);

        return back()->with('success', 'Profil mis à jour.');
    }
}
