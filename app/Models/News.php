<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class News extends Model
{
    protected $fillable = ['establishment_id', 'title', 'content', 'photo', 'expires_at'];

    protected function casts(): array
    {
        return ['expires_at' => 'date'];
    }

    public function establishment(): BelongsTo
    {
        return $this->belongsTo(Establishment::class);
    }
}
