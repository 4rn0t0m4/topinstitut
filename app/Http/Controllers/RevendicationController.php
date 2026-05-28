<?php

namespace App\Http\Controllers;

use App\Http\Concerns\RespondsJsonOrBack;
use App\Http\Requests\StoreClaimRequest;
use App\Models\Claim;
use App\Models\Establishment;
use App\Services\ClaimService;

class RevendicationController extends Controller
{
    use RespondsJsonOrBack;

    public function __construct(private ClaimService $claims) {}

    public function store(StoreClaimRequest $request, Establishment $establishment)
    {
        if ($this->claims->isAlreadyOwner($request->user(), $establishment)) {
            return $this->errorJsonOrBack($request, ['email' => 'Vous êtes déjà propriétaire de cet établissement.']);
        }

        if ($this->claims->hasPendingClaim($establishment, $request->input('email'))) {
            return $this->errorJsonOrBack($request, ['email' => 'Une demande pour cet établissement est déjà en cours avec cet email.']);
        }

        $message = $this->claims->submit($establishment, $request->user(), $request->validated());

        return $this->successJsonOrBack($request, $message);
    }

    public function confirmEmail(string $token)
    {
        $claim = Claim::where('verification_token', $token)
            ->whereNull('email_verified_at')
            ->with('establishment')
            ->firstOrFail();

        $this->claims->verifyEmail($claim);

        return redirect($claim->establishment->url)
            ->with('success', 'Votre email a été vérifié. Votre revendication sera traitée par notre équipe sous peu.');
    }
}
