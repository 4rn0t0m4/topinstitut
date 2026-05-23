<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewVote;
use App\Services\RatingService;
use App\Services\ReviewPhotoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AvisController extends Controller
{
    public function store(Request $request, RatingService $ratingService, ReviewPhotoService $reviewPhotoService)
    {
        $rules = [
            'establishment_id' => 'required|exists:establishments,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:5000',
            'rating_welcome' => 'required|integer|min:1|max:5',
            'rating_quality' => 'required|integer|min:1|max:5',
            'rating_variety' => 'required|integer|min:1|max:5',
            'rating_price' => 'required|integer|min:1|max:5',
            'rating_ambiance' => 'required|integer|min:1|max:5',
            'rating_cleanliness' => 'required|integer|min:1|max:5',
            'photos' => 'nullable|array|max:3',
            'photos.*' => 'image|mimes:jpeg,jpg,png,webp|max:5120', // 5 Mo
        ];

        if (! $request->user()) {
            $rules['author_name'] = 'required|string|max:255';
            $rules['author_email'] = 'required|email|max:255';
        }

        $validated = $request->validate($rules);
        unset($validated['photos']);
        $validated['ip'] = $request->ip();

        if ($request->user()) {
            $validated['user_id'] = $request->user()->id;
            $validated['email_verified_at'] = now();
        } else {
            $validated['verification_token'] = Str::random(64);
        }

        $review = Review::create($validated);

        // Upload photos sur R2 (converties en WebP, 1200px max)
        if ($request->hasFile('photos')) {
            $reviewPhotoService->upload($review, $request->file('photos'));
        }

        if (! $request->user()) {
            $this->sendConfirmationEmail($review);

            return back()->with('success', 'Merci ! Un email de confirmation vous a été envoyé à '.$review->author_email.'. Veuillez cliquer sur le lien pour valider votre avis.');
        }

        return back()->with('success', 'Votre avis a été soumis et sera publié après modération.');
    }

    public function confirmEmail(string $token)
    {
        $review = Review::where('verification_token', $token)
            ->whereNull('email_verified_at')
            ->firstOrFail();

        $review->update([
            'email_verified_at' => now(),
            'verification_token' => null,
        ]);

        return redirect($review->establishment->url)
            ->with('success', 'Votre email a été confirmé. Votre avis sera publié après modération par notre équipe.');
    }

    public function toggleHelpful(Request $request)
    {
        $request->validate([
            'review_id' => 'required|exists:reviews,id',
            'is_helpful' => 'required|boolean',
        ]);

        ReviewVote::updateOrCreate(
            ['review_id' => $request->review_id, 'user_id' => $request->user()->id],
            ['is_helpful' => $request->boolean('is_helpful')]
        );

        return response()->json(['ok' => true]);
    }

    private function sendConfirmationEmail(Review $review): void
    {
        $url = route('review.confirm', $review->verification_token);
        $establishment = $review->establishment->name;

        Mail::send('emails.review-confirmation', [
            'name' => $review->author_name,
            'establishment' => $establishment,
            'url' => $url,
        ], function ($message) use ($review) {
            $message->to($review->author_email)
                ->subject('Confirmez votre avis sur Top Institut');
        });
    }
}
