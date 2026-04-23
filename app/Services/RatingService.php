<?php

namespace App\Services;

use App\Models\Establishment;

class RatingService
{
    public function recalculate(Establishment $establishment): void
    {
        $reviews = $establishment->approvedReviews;

        if ($reviews->isEmpty()) {
            $establishment->update(['rating' => 0, 'review_count' => 0]);

            return;
        }

        $total = $reviews->sum(fn ($r) => $r->average_rating);
        $rating = round($total / $reviews->count(), 1);

        $establishment->update([
            'rating' => $rating,
            'review_count' => $reviews->count(),
        ]);
    }
}
