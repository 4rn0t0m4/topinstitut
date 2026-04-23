<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Faq extends Model
{
    protected $fillable = ['establishment_id', 'question', 'answer', 'sort_order'];

    public function establishment(): BelongsTo
    {
        return $this->belongsTo(Establishment::class);
    }
}
