<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewPhoto extends Model
{
    protected $fillable = ['review_id', 'filename', 'sort_order'];

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    protected function url(): Attribute
    {
        return Attribute::get(fn () => rtrim(config('filesystems.disks.r2.url'), '/')
            ."/reviews/{$this->review_id}/{$this->filename}");
    }
}
