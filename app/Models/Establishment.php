<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Establishment extends Model
{
    use HasFactory, Notifiable;

    const TYPE_SLUGS = [
        0 => 'institut-de-beaute',
        1 => 'estheticienne-a-domicile',
        2 => 'spa',
        3 => 'thalasso',
    ];

    const TYPE_LABELS = [
        0 => 'Institut de beauté',
        1 => 'Esthéticienne à domicile',
        2 => 'Spa',
        3 => 'Thalasso',
    ];

    /**
     * Caractéristiques pratiques (filtres). Stockées en JSON dans `features`.
     */
    const FEATURES = [
        'pmr' => 'Accessible PMR',
        'men' => 'Hommes acceptés',
        'organic' => 'Produits bio / vegan',
        'parking' => 'Parking',
        'english' => 'Anglais parlé',
        'cb' => 'Carte bancaire acceptée',
        'gift_card' => 'Bons cadeaux',
    ];

    /**
     * Resolve a type slug to its numeric ID (or null if unknown).
     */
    public static function typeIdFromSlug(string $slug): ?int
    {
        $id = array_search($slug, self::TYPE_SLUGS, true);

        return $id === false ? null : $id;
    }

    /**
     * Route notifications to the establishment's contact email.
     */
    public function routeNotificationForMail(): ?string
    {
        return $this->email;
    }

    protected $fillable = [
        'type', 'name', 'slug', 'email', 'website', 'google_maps_url',
        'address', 'postal_code', 'city', 'department_code', 'city_id',
        'latitude', 'longitude', 'radius',
        'description', 'pricing', 'phone', 'mobile',
        'siret', 'photo', 'tagline', 'is_active', 'rating', 'review_count', 'view_count',
        'features',
        'subscription_tier', 'subscription_ends_at', 'is_verified_owner', 'featured_until',
        'stripe_subscription_id', 'trial_started_at',
        'google_place_id', 'google_rating', 'google_review_count', 'google_reviews',
        'google_photos_checked_at', 'google_reviews_checked_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => 'integer',
            'is_active' => 'boolean',
            'rating' => 'decimal:1',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'google_reviews' => 'array',
            'google_photos_checked_at' => 'datetime',
            'google_reviews_checked_at' => 'datetime',
            'features' => 'array',
            'is_verified_owner' => 'boolean',
            'subscription_ends_at' => 'datetime',
            'featured_until' => 'datetime',
            'trial_started_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Establishment $e) {
            // Si city_id est lié et department_code absent, on déduit depuis la ville.
            if ($e->city_id && empty($e->department_code)) {
                $code = City::where('id', $e->city_id)->value('department_code');
                if ($code) {
                    $e->department_code = $code;
                }
            }
        });
    }

    // --- Accessors ---

    public function getOpeningStatusAttribute(): string
    {
        $dayOfWeek = now()->dayOfWeekIso;
        $schedule = $this->schedules->firstWhere('day_of_week', $dayOfWeek);

        if (! $schedule) {
            return 'unknown';
        }

        return $schedule->status;
    }

    public function getNextOpeningAttribute(): ?string
    {
        $now = now();
        $currentDay = $now->dayOfWeekIso;
        $minutes = $now->hour * 60 + $now->minute;

        for ($i = 0; $i < 7; $i++) {
            $day = (($currentDay - 1 + $i) % 7) + 1;
            $schedule = $this->schedules->firstWhere('day_of_week', $day);

            if (! $schedule || $schedule->is_closed) {
                continue;
            }

            foreach ([$schedule->open_am, $schedule->open_pm] as $openTime) {
                if (! $openTime) {
                    continue;
                }

                $parts = explode(':', $openTime);
                $openMinutes = ((int) $parts[0]) * 60 + ((int) ($parts[1] ?? 0));
                $formatted = substr($openTime, 0, 5);

                if ($i === 0) {
                    if ($openMinutes > $minutes) {
                        return "Ouvre à $formatted";
                    }

                    continue;
                }

                return 'Ouvre '.Schedule::DAYS[$day]." à $formatted";
            }
        }

        return null;
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->type] ?? 'Institut de beauté';
    }

    public function getTypeSlugAttribute(): string
    {
        return self::TYPE_SLUGS[$this->type] ?? 'institut-de-beaute';
    }

    public function getUrlAttribute(): string
    {
        $city = $this->cityRelation;
        $deptSlug = $city?->department?->slug;
        $citySlug = $city?->slug;

        // Fallback to legacy flat URL if dept/city not resolvable
        if (! $deptSlug || ! $citySlug) {
            return '/'.$this->type_slug.'/'.$this->slug;
        }

        return '/'.$deptSlug.'/'.$citySlug.'/'.$this->type_slug.'/'.$this->slug;
    }

    /**
     * Abonnement Premium actif ? Tier = 'premium' ET subscription_ends_at futur (ou null = sans limite).
     */
    public function getIsPremiumAttribute(): bool
    {
        if ($this->subscription_tier !== 'premium') {
            return false;
        }

        return $this->subscription_ends_at === null || $this->subscription_ends_at->isFuture();
    }

    /**
     * Période d'essai active : trial démarré, pas encore expirée, et pas de paiement Stripe.
     */
    public function getIsInTrialAttribute(): bool
    {
        return $this->trial_started_at !== null
            && empty($this->stripe_subscription_id)
            && $this->is_premium;
    }

    /**
     * Jours restants dans l'essai (0 si expiré ou pas en essai).
     */
    public function getTrialDaysLeftAttribute(): int
    {
        if (! $this->is_in_trial || ! $this->subscription_ends_at) {
            return 0;
        }

        return max(0, (int) ceil(now()->diffInDays($this->subscription_ends_at, false)));
    }

    /**
     * Démarre la période d'essai (1 mois) si l'établissement n'en a jamais eu et n'est pas Premium.
     */
    public function startTrialIfEligible(): bool
    {
        if ($this->trial_started_at || $this->is_premium) {
            return false;
        }

        $this->update([
            'subscription_tier' => 'premium',
            'subscription_ends_at' => now()->addMonth(),
            'trial_started_at' => now(),
        ]);

        return true;
    }

    /**
     * Mise en avant payante active dans la ville/recherche ?
     */
    public function getIsFeaturedAttribute(): bool
    {
        return $this->featured_until !== null && $this->featured_until->isFuture();
    }

    /**
     * Limite de photos affichables selon le tier.
     */
    public function getMaxDisplayedPhotosAttribute(): int
    {
        return $this->is_premium ? 100 : 3;
    }

    // --- Scopes ---

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Tri prioritaire pour les listings : featured > premium > rating.
     */
    public function scopeOrderedForListing(Builder $query): Builder
    {
        $now = now();

        return $query
            ->orderByRaw('CASE WHEN featured_until IS NOT NULL AND featured_until > ? THEN 0 ELSE 1 END', [$now])
            ->orderByRaw("CASE WHEN subscription_tier = 'premium' AND (subscription_ends_at IS NULL OR subscription_ends_at > ?) THEN 0 ELSE 1 END", [$now])
            ->orderByDesc('rating')
            ->orderByDesc('review_count');
    }

    public function scopeByType(Builder $query, int $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeNearby(Builder $query, float $lat, float $lng, float $radiusKm = 10): Builder
    {
        // Bounding box + squared-distance ordering: portable across MySQL/SQLite,
        // index-friendly, and close enough to a true great-circle for directory listings.
        $deltaLat = $radiusKm / 111.0;
        $deltaLng = $radiusKm / max(0.01, 111.0 * abs(cos(deg2rad($lat))));

        return $query
            ->whereBetween('latitude', [$lat - $deltaLat, $lat + $deltaLat])
            ->whereBetween('longitude', [$lng - $deltaLng, $lng + $deltaLng])
            ->orderByRaw(
                '(latitude - ?) * (latitude - ?) + (longitude - ?) * (longitude - ?)',
                [$lat, $lat, $lng, $lng]
            );
    }

    public function scopeOpenNow(Builder $query): Builder
    {
        $dayOfWeek = now()->dayOfWeekIso;
        $time = now()->format('H:i:s');

        return $query->whereHas('schedules', function (Builder $q) use ($dayOfWeek, $time) {
            $q->where('day_of_week', $dayOfWeek)
                ->where('is_closed', false)
                ->where(function (Builder $q) use ($time) {
                    $q->where(function (Builder $q) use ($time) {
                        $q->where('open_am', '<=', $time)->where('close_am', '>', $time);
                    })->orWhere(function (Builder $q) use ($time) {
                        $q->where('open_pm', '<=', $time)->where('close_pm', '>', $time);
                    });
                });
        });
    }

    public function scopeWithPhotos(Builder $query): Builder
    {
        return $query->whereHas('photos');
    }

    public function scopeMinRating(Builder $query, float $min): Builder
    {
        return $query->where(function (Builder $q) use ($min) {
            $q->where('rating', '>=', $min)->orWhere('google_rating', '>=', $min);
        });
    }

    // --- Relations ---

    public function cityRelation(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_establishment');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class)->orderBy('sort_order');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('is_approved', true)->where('is_rejected', false);
    }

    public function news(): HasMany
    {
        return $this->hasMany(News::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class)->orderBy('day_of_week');
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class)->orderBy('sort_order');
    }

    public function serviceCategories(): HasMany
    {
        return $this->hasMany(ServiceCategory::class)->orderBy('sort_order');
    }

    public function practitioners(): HasMany
    {
        return $this->hasMany(Practitioner::class)->orderBy('sort_order');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function getAcceptsBookingsAttribute(): bool
    {
        return $this->is_premium
            && $this->practitioners->where('is_active', true)->isNotEmpty()
            && $this->services->where('is_bookable', true)->isNotEmpty();
    }

    public function owners(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function slugHistory(): HasMany
    {
        return $this->hasMany(EstablishmentSlug::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class)->orderBy('sort_order');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }
}
