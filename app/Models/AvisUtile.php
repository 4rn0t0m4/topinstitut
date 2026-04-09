<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvisUtile extends Model
{
    protected $table = 'avis_utiles';

    protected $fillable = ['avis_id', 'user_id', 'utile'];

    protected function casts(): array
    {
        return ['utile' => 'boolean'];
    }

    public function avis(): BelongsTo
    {
        return $this->belongsTo(Avis::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
